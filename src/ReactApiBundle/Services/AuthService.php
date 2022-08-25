<?php

namespace ReactApiBundle\Services;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use AppBundle\Repository\DocumentClientRepository;
use AppBundle\Repository\VideoClientRepository;
use AppBundle\Repository\ClientRepository;
use AppBundle\Services\TrainerAssetsService;
use ProgressBundle\Services\ClientProgressService;
use AppBundle\Services\ClientService;
use AppBundle\Entity\Client;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AuthService
{
    private EntityManagerInterface $em;
    private ClientRepository $clientRepository;
    private VideoClientRepository $videoClientRepository;
    private DocumentClientRepository $documentClientRepository;
    private TrainerAssetsService $trainerAssetsService;
    private ClientService $clientService;
    private ClientProgressService $clientProgressService;
    private string $appHostname;

    public function __construct(
        EntityManagerInterface $em,
        TrainerAssetsService $trainerAssetsService,
        ClientService $clientService,
        ClientProgressService $clientProgressService,
        ClientRepository $clientRepository,
        VideoClientRepository $videoClientRepository,
        DocumentClientRepository $documentClientRepository,
        string $appHostname
    ) {
        $this->em = $em;
        $this->trainerAssetsService = $trainerAssetsService;
        $this->clientService = $clientService;
        $this->clientProgressService = $clientProgressService;
        $this->documentClientRepository = $documentClientRepository;
        $this->videoClientRepository = $videoClientRepository;
        $this->clientRepository = $clientRepository;
        $this->appHostname = $appHostname;
    }

    public function login($email, $password)
    {
        $client = $this
            ->clientRepository
            ->findOneBy([
                'email' => $email
            ], [
                'id' => 'DESC'
            ]);

        if ($client === null) {
            throw new HttpException(Response::HTTP_UNPROCESSABLE_ENTITY, 'No client with this e-mail / password combination.');
        }

        $clientPassword = $client->getPassword();
        if ($clientPassword === null || !password_verify($password, $clientPassword)) {
            throw new HttpException(Response::HTTP_UNPROCESSABLE_ENTITY, 'No client with this e-mail / password combination.');
        }

        if (!$client->getAccessApp()) {
            throw new HttpException(Response::HTTP_UNPROCESSABLE_ENTITY, 'The credentials are correct, but it looks like you have been deactivated - please contact your coach.');
        }

        return $client;
    }

    /** @return array<string, mixed> */
    public function getClientData(Client $client): array
    {
        static $result = null;

        if ($result === null) {

            $clientProgress = $this
                ->clientProgressService
                ->setClient($client)
                ->setProgressValues()
                ->setUnits()
                ->getProgress(true);

            $progress = collect($clientProgress)
                ->only(['direction', 'left', 'progress', 'percentage', 'weekly', 'weeks', 'last', 'start', 'goal'])
                ->all();

            $totalDocuments = $this
                ->documentClientRepository
                ->countByClient($client);

            $newVideos = $this
                ->videoClientRepository
                ->getNewVideosByClient($client);

            $token = $client->getToken();
            if ($token == null) {
                $random = random_bytes(32);
                $token = bin2hex($random);
                $client->setToken($token);
                $this->em->flush();
            }

            $result = [
                'id' => $client->getId(),
                'token' => $token,
                'email' => $client->getEmail(),
                'firstName' => $client->getFirstName(),
                'name' => $client->getName(),
                'gender' => $client->getGender(),
                'startDate' => $client->getStartDate(),
                'measuring' => $client->getMeasuringSystem(),
                'lastProgressActivity' => $client->getBodyProgressUpdated(),
                'trackDayProgress' => $client->getDayTrackProgress(),
                'trackDayMacros' => null,
                'totalDocuments' => (int) $totalDocuments,
                'totalVideos' => (int) $newVideos,
                'height' => (double)$client->getHeight(),
                'weight' => $progress['last'],
                'initialWeight' => $progress['start'],
                'targetWeight' => $progress['goal'],
                'primaryGoal' => $client->getPrimaryGoal(),
                'growth' => $progress,
                'trainer' => $this->getClientTrainerInfo($client),
                'questionnaire' => $client->getQuestionnaireSettings($this->appHostname),
                'kcalNeed' => (int) $this->clientService->getKcalNeed($client),
                'avgMacros' => null,
                'accessApp' => $client->getAccessApp(),
                'activated' => $client->hasBeenActivated()
            ];
        }

        return $result;
    }

    /** @return array<string, mixed> */
    protected function getClientTrainerInfo(Client $client): array
    {
        $trainer = $client->getUser();
        $video = null;
        $youtubeId = null;
        $trainerAssets = $this->trainerAssetsService;
        $trainerSettings = $trainerAssets->getUserSettings($client->getUser());

        //get trainer's intro video
        try {
            $video = $trainerAssets->getUserIntroVideo($client->getUser());
            if ($video !== null) {
                $youtubeId = $trainerAssets->getYoutubeId($video);
            }
        } catch (\Exception) {
            $video = null;
            $youtubeId = null;
        }

        $message = $trainerSettings->getWelcomeMessage();
        if ($message && $message !== "") {
            $message = strip_tags($message, '<br>');
            $breaks = array("<br />", "<br>", "<br/>");
            $message = str_ireplace($breaks, "\r\n", $message);
        } else {
            $message = '';
        }

        $checkInQuestions = null;

        if ($checkIn = $trainerSettings->getCheckInQuestions()) {
            $content = json_decode($checkIn, true);
            $checkInQuestions = $content;
        };

        return [
            'firstName' => $trainer->getFirstName(),
            'lastName' => $trainer->getLastName(),
            'picture' => $trainerSettings->getProfilePicture(),
            'company' => $trainerSettings->getCompanyName(),
            'companyLogo' => $trainerSettings->getCompanyLogo(),
            'video' => $video,
            'videoThumb' => $trainerSettings->getVideoThumb(),
            'phone' => null,
            'email' => $trainer->getEmail(),
            'youtubeId' => $youtubeId,
            'message' => $message,
            'checkInQuestions' => $checkInQuestions,
            'hideNutritionalFactsInApp' => $trainer->getHideNutrionalFactsInApp(),
            'checkInMessageMandatory' => $trainerSettings->getCheckInMessageMandatory(),
            'primaryColor' => $trainerSettings->getPrimaryColor(),
            'askForPeriod' => $trainerSettings->getAskForPeriod(),
            'showFatPercentage' => $trainerSettings->getShowFatPercentage(),
            'checkInDuration' => $trainerSettings->getCheckInDuration()
        ];
    }
}
