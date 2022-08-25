<?php declare(strict_types=1);

namespace AppBundle\Services;

use AppBundle\Entity\Client;
use AppBundle\Entity\User;
use AppBundle\PushMessages\PushNotificationServiceException;
use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Support\Arr;
use OneSignal\Config;
use Psr\Log\LoggerInterface;
use OneSignal\OneSignal;
use Symfony\Component\HttpClient\Psr18Client;

class PushNotificationService
{
    protected EntityManagerInterface $em;
    protected OneSignal $api;
    private LoggerInterface $logger;

    public function __construct(EntityManagerInterface $em, LoggerInterface $logger, OneSignal $onesignal)
    {
        $this->api = $onesignal;
        $this->em = $em;
        $this->logger = $logger;
    }

    public function send(array $data, ?User $user = null): array
    {
        if ($user && ($userApp = $user->getUserApp())) {
            $applicationId = $userApp->getOnesignalAppId();
            $applicationAuthKey = $userApp->getOnesignalAppKey();

            if ($applicationId && $applicationAuthKey) {
                $this->setupConnection($applicationId, $applicationAuthKey);
            }
        }

        $response = $this->api->notifications()->add($data);
        $errors = Arr::get($response, 'errors', []);
        $recipients = Arr::get($response, 'recipients', 0);

        if (count($errors) > 0 && $recipients > 0) {
            $err = $errors[0];

            if ($err === 'All included players are not subscribed') {
                $err = 'The client hasn\'t accepted Zenfit push notifications - tell them to do so :)';
            }

            $error = new PushNotificationServiceException($err);
            $error->setResponse($response);

            $this->logError($data, $errors, $error);

            throw $error;
        }

        return $response;
    }

    public function sendToClient(Client $client, array|string $contents, array $data = [], array $filters = [], User $user = null, $screen = 'Messages'): array
    {
        if (is_string($contents)) {
            $contents = [
                'en' => $contents,
            ];
        }

        $data = array_merge($data, [
            'contents' => $contents,
            'data' => array('screen' => $screen),
            'filters' => array_merge($filters, [
                [
                    'field' => 'tag',
                    'key' => 'clientId',
                    'relation' => '=',
                    'value' => $client->getId(),
                ],
            ]),
        ]);

        if (!$user) {
            $user = $client->getUser();
        }

        return $this->send($data, $user);
    }

    public function getClientsThatShouldReceiveWeeklyReminder(int $day): array
    {
        return $this->em->getRepository(Client::class)->findBy([
            'acceptEmailNotifications' => true,
            'deleted' => false,
            'active' => true,
            'dayTrackProgress' => $day
        ]);
    }

    protected function setupConnection(string $applicationId, string $applicationAuthKey): void
    {
        $config = new Config($applicationId, $applicationAuthKey);

        $this->api = new OneSignal($config, new Psr18Client(), new Psr18Client(), new Psr18Client());
    }

    private function logError(array $data, array $errors, PushNotificationServiceException $error): void
    {
        $data = [
            'class' => __CLASS__,
            'data' => $data,
            'errors' => $errors,
            'error' => $error->getMessage(),
            'response' => $error->getResponse(),
        ];

        $this->logger->error(json_encode($data));
    }
}
