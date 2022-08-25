<?php declare(strict_types=1);

namespace AppBundle\Services;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class TranslationService
{
    const COUNTRY_LOCALE_NAMES = [
        'Denmark' => 'Danish',
        'Sweden' => 'Swedish',
        'Norway' => 'Norwegian',
        'Finland' => 'Finnish',
        'Netherlands' => 'Dutch',
        'Germany' => 'German'
    ];

    const DEFAULT_LOCALE_NAME = 'English';

    private TranslatorInterface $translator;
    private RequestStack $requestStack;
    private UrlGeneratorInterface $urlGenerator;
    private string $ipdataApiKey;
    private HttpClientInterface $httpClient;

    public function __construct(
        TranslatorInterface $translator,
        HttpClientInterface $httpClient,
        RequestStack $requestStack,
        string $ipdataApiKey,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->httpClient = $httpClient;
        $this->ipdataApiKey = $ipdataApiKey;
        $this->translator = $translator;
        $this->urlGenerator = $urlGenerator;
        $this->requestStack = $requestStack;
    }

    /**
     * @return array
     */
    public function getChoices()
    {
        return [
            'English' => 'en',
            'Danish' => 'da_DK',
            'Swedish' => 'sv_SE',
            'Norwegian' => 'nb_NO',
            'Finnish' => 'fi_FI',
            'Dutch' => 'nl_NL',
            'German' => 'de_DE'
        ];
    }

    /**
     * @return array
     */
    public function getReadables()
    {
        return  [
            'en' => 'English',
            'da_DK' => 'Danish',
            'sv_SE' => 'Swedish',
            'nb_NO' => 'Norwegian',
            'fi_FI' => 'Finnish',
            'nl_NL' => 'Dutch',
            'de_DE' => 'German'
        ];
    }

    /**
     * @param null $locale
     * @param string $defaultLocale
     */
    public function setLocale($locale = null, $defaultLocale = 'en')
    {
        if (!$locale) {
            $choices = $this->getChoices();

            $locale = rescue(function () use ($choices, $defaultLocale) {
                $country = $this->getCountry();
                $localeName = static::COUNTRY_LOCALE_NAMES[$country] ?? static::DEFAULT_LOCALE_NAME;

                return $choices[$localeName] ?? $defaultLocale;
            });
        }

        $this->translator->setLocale($locale);
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->translator
            ->getLocale();
    }

    public function detectByCountry(?string $default): ?string
    {
        return rescue(function () use ($default) {
            $country = $this->getCountry();
            $choices = $this->getChoices();
            $localeName = static::COUNTRY_LOCALE_NAMES[$country] ?? static::DEFAULT_LOCALE_NAME;

            return $choices[$localeName] ?? $default;
        }, $default);
    }

    /**
     * @param string $locale
     * @return string
     */
    public function getUrl($locale = null)
    {
        if (null === $locale) {
            $locale = $this->getLocale();
        }

        $request = $this->requestStack
            ->getCurrentRequest();

        $parameters = array_merge($request->query->all(), [
            'locale' => $locale,
        ]);

        $url = $this->urlGenerator
            ->generate($request->get('_route'), $parameters);

        return $url;
    }

    /**
     * @return string|null
     */
    protected function getCountry()
    {
        static $data = null;

        if (null === $data) {
            $data = rescue(function () {
                $ip = $this->getIP();

                if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return null;
                }

                $url = "https://api.ipdata.co/$ip?api-key={$this->ipdataApiKey}";
                $res = $this->httpClient->request('GET', $url);

                if (Response::HTTP_OK === $res->getStatusCode()) {
                    return json_decode($res->getContent(), true, 512, JSON_THROW_ON_ERROR);
                }

                return null;
            });
        }

        return $data['country_name'] ?? null;
    }

    /**
     * @return string|null
     */
    protected function getIP()
    {
        if (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER)) {
            return $_SERVER["HTTP_X_FORWARDED_FOR"];
        }
        if (array_key_exists('REMOTE_ADDR', $_SERVER)) {
            return $_SERVER["REMOTE_ADDR"];
        }

        if (array_key_exists('HTTP_CLIENT_IP', $_SERVER)) {
            return $_SERVER["HTTP_CLIENT_IP"];
        }

        return $this->requestStack
            ->getCurrentRequest()
            ->getClientIp();
    }
}
