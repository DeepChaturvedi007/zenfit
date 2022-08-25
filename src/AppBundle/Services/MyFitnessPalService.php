<?php declare(strict_types=1);

namespace AppBundle\Services;

use AppBundle\Entity\ClientSettings;
use AppBundle\Entity\Client;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class MyFitnessPalService
{
    private EntityManagerInterface $em;
    private ErrorHandlerService $errorHandlerService;
    private string $appBaseUrl;
    private string $appClientId;
    private string $appClientSecret;
    private int $appApiVersion;
    private int $requestsPerSecond;
    private string $appRedirectURI;
    private ?ClientSettings $clientSettings = null;
    private HttpClientInterface $httpClient;
    private float $lastRequestTimestamp;
    private int $requestsCounter = 0;

    public const APP_SCOPE = 'diary';
    public const REFRESH_TOKEN_THRESHOLD = 259200; // in seconds

    /** @param array<int|string> $myFitnessPalConfig */
    public function __construct(
        EntityManagerInterface $em,
        HttpClientInterface $httpClient,
        ErrorHandlerService $errorHandlerService,
        array $myFitnessPalConfig
    ) {
        $this->em = $em;
        $this->httpClient = $httpClient;
        $this->errorHandlerService = $errorHandlerService;
        $this->appBaseUrl = (string) $myFitnessPalConfig['base_url'];
        $this->appClientId = (string) $myFitnessPalConfig['client_id'];
        $this->appClientSecret = (string) $myFitnessPalConfig['client_secret'];
        $this->appApiVersion = (int) $myFitnessPalConfig['api_version'];
        $this->appRedirectURI = (string) $myFitnessPalConfig['redirect_uri'];
        $this->requestsPerSecond = (int) $myFitnessPalConfig['requests_per_second'];
        $this->lastRequestTimestamp = 0;
    }

    public function setClientSettings(ClientSettings $clientSettings): self
    {
        $this->clientSettings = $clientSettings;

        return $this;
    }

    public function getClientSettings(): ?ClientSettings
    {
        return $this->clientSettings;
    }

    /** @param array<mixed> $params */
    protected function buildUrl(string $path, array $params = []): string
    {
        return rtrim($this->appBaseUrl, '/')
            . "/v{$this->appApiVersion}"
            . "/" . trim($path, '/')
            . (($params) ? '?'.http_build_query($params) : '')
        ;
    }

    public function getAuthUrl(int $clientId): string
    {
        return $this->buildUrl('/oauth2/auth', [
            'client_id' => $this->appClientId,
            'response_type' => 'code',
            'scope' => self::APP_SCOPE,
            'redirect_uri' => $this->appRedirectURI,
            'state' => $clientId,
        ]);
    }

    public function isClientIntegratedWithMFP(Client $client): bool
    {
        $clientSettings = $client->getClientSettings();

        return $clientSettings && $clientSettings->getMfpAccessToken();
    }

    /**
     * @param array<string, string> $headers
     * @param array<string, string> $formParams
     * @return array<mixed>
     */
    public function request(string $url, string $method = 'GET', array $headers = [], array $formParams = []): array
    {
        $defaultHeaders = [
            'Accept' => 'application/json',
            'Accept-Encoding' => 'gzip,deflate,sdch',
            'Accept-Language' => 'en-US,en;q=0.8',
            'mfp-client-id' => $this->appClientId,
        ];

        $clientSettings = $this->getClientSettings();
        if ($clientSettings) {
            $mfpUserId = $clientSettings->getMfpUserId();
            $mfpAccessToken = $clientSettings->getMfpAccessToken();

            if ($mfpUserId) {
                $defaultHeaders['mfp-user-id'] = $mfpUserId;
                $defaultHeaders['Authorization'] = "Bearer {$mfpAccessToken}";
            }
        }

        $requestHeaders = array_merge($defaultHeaders, $headers);

        $params = ['headers' => $requestHeaders];
        if ($formParams) {
            $params['body'] = $formParams;
        }

        if ((microtime(true) - $this->lastRequestTimestamp) < 1) {
            if ($this->requestsCounter >= $this->requestsPerSecond) {
                $remainingTime = (float) max(0, 1 - (microtime(true) - $this->lastRequestTimestamp));
                $this->sleep($remainingTime);
            }
        } else {
            $this->lastRequestTimestamp = 0;
            $this->requestsCounter = 0;
        }

        $this->requestsCounter++;
        $this->lastRequestTimestamp = microtime(true);

        $result = $this->httpClient->request($method, $url, $params)->getContent();

        return json_decode($result, true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @param float $value
     */
    private function sleep(float $value): void
    {
        $values = explode('.', (string) $value);
        $seconds = array_shift($values);
        $milliseconds = array_shift($values);
        \sleep((int) $seconds);
        if (null !== $milliseconds) {
            $milliseconds = ((float) sprintf('0.%s', $milliseconds)) * 1000;
            usleep((int) ($milliseconds * 1000));
        }
    }

    public function authMfpClient(string $state, string $code): void
    {
        /** @var ?Client $client */
        $client = $this->em->getRepository(Client::class)->find($state);

        if ($client === null) {
            throw new \RuntimeException("Unauthorized. Client {$state} not found.");
        }

        $clientSettings = $client->getClientSettings();
        if (!$clientSettings) {
            $clientSettings = new ClientSettings($client);
            $this->setClientSettings($clientSettings);
        }

        $this->getAuthTokenForSetting([
            'grant_type' => 'authorization_code',
            'code' => $code,
        ], $clientSettings, $client);
    }

    /** @return array<mixed> */
    public function getDiaryMeals(\DateTime $date): array
    {
        $meals = [];

        $requestUrl = $this->buildUrl('/diary', [
            'entry_date' => $date->format('Y-m-d'),
            'types' => 'diary_meal',
            'fields' => ['nutritional_contents', 'energy'],
        ]);

        try {
            $response = $this->request($requestUrl);
            if (!empty($response['items'])) {
                $meals = $response['items'];
            }
        } catch (\Exception $e) {
            $this->errorHandlerService->captureException($e);
        }


        return $meals;
    }

    public function refreshToken(): void
    {
        $clientSettings = $this->getClientSettings();
        if ($clientSettings === null) {
            throw new NotFoundHttpException('No ClientSettings object');
        }

        $this->getAuthTokenForSetting([
            'grant_type' => 'refresh_token',
            'refresh_token' => $clientSettings->getMfpRefreshToken(),
        ], $clientSettings, $clientSettings->getClient());
    }

    /** @param array<string, string> $params */
    private function getAuthTokenForSetting(array $params, ClientSettings $clientSettings, Client $client): void
    {
        try {
            $response = $this->request($this->buildUrl('/oauth2/token'), 'POST', [], array_merge([
                'redirect_uri' => $this->appRedirectURI,
                'client_id' => $this->appClientId,
                'client_secret' => $this->appClientSecret,
            ], $params));

            if (empty($response['access_token'])) {
                throw new \Exception('Unable to get access token');
            }

            $expiresIn = (int)($response['expires_in'] ?? 0);
            $expiresIn = ($expiresIn >= 0) ? $expiresIn : 0;
            $expireDate = (new \DateTime())->add(new \DateInterval('PT' . $expiresIn . 'S'));

            $clientSettings
                ->setClient($client)
                ->setMfpUserId($response['user_id'])
                ->setMfpAccessToken($response['access_token'])
                ->setMfpRefreshToken($response['refresh_token'])
                ->setMfpExpireDate($expireDate);

            $this->em->persist($clientSettings);
            $this->em->flush();
        } catch (\Exception $e) {
            $this->errorHandlerService->captureException($e);
        }
    }
}
