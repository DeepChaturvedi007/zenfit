<?php

namespace ProgressBundle\Controller;

use AppBundle\Entity\Client;
use AppBundle\Entity\ClientSettings;
use AppBundle\Entity\User;
use AppBundle\Services\ClientImageService;
use AppBundle\Services\ClientService;
use ProgressBundle\Services\ClientProgressService;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response as Response;
use AppBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class DefaultController extends Controller
{
    private ClientImageService $clientImageService;
    private ClientProgressService $clientProgressService;
    private ClientService $clientService;
    private string $s3beforeAfterImages;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        ClientService $clientService,
        ClientImageService $clientImageService,
        EntityManagerInterface $em,
        string $s3beforeAfterImages,
        ClientProgressService $clientProgressService
    ) {
        $this->clientImageService = $clientImageService;
        $this->clientProgressService = $clientProgressService;
        $this->clientService = $clientService;
        $this->s3beforeAfterImages = $s3beforeAfterImages;

        parent::__construct($em, $tokenStorage);
    }

    /**
     * @param Client $client
     * @return Response
     * @Route("/client/{client}", name="clientProgress")
     */
    public function indexAction(Client $client)
    {
        $currentUser = $this->getUser();
        if (!$currentUser instanceof User) {
            throw new AccessDeniedHttpException();
        }

        $user = $currentUser->isAssistant() ? $currentUser->getGymAdmin() : $currentUser;

        $valid = $this->clientBelongsToUser($client);
        if (!$valid) {
            return $this->redirectToRoute('clients');
        }

        $clientProgressService = $this->clientProgressService;
        $clientProgressService
            ->setClient($client)
            ->setProgressValues()
            ->setUnits();

        $imageUrl = $this->s3beforeAfterImages;

        $progress = $clientProgressService->getProgress();
        $progress['last'] = $clientProgressService->getLastEntries(5, 0, 'DESC');
        $progress['pictures'] = $this->clientImageService->getLast($client, 2)->toArray();
        $progress['totalEntries'] = $clientProgressService->getTotalEntries();

        $collectChartData = function ($property) use ($progress) {
            return $progress['entries']->mapWithKeys(function ($item) use ($property) {
                return [ucfirst($item['date']) => $item[$property]];
            })->filter(function($item) {
                return $item != null;
            });
        };

        $charts = [
            'weight' => $collectChartData('weight'),
            'circumference' => $collectChartData('total'),
            'fat' => $collectChartData('fat'),
        ];

        $clientStatus = $clientProgressService->getClientStatus();

        $clientService = $this->clientService;
        $kcalNeed = (int)$clientService->getKcalNeed($client);

        $kpi = [
            'circumference' => $clientProgressService->getCircumferenceProgress(),
            'fat' => $clientProgressService->getFatProgress(),
            'weight' => $clientProgressService->getWeightProgress(),
        ];

        /** @var ClientSettings $clientSettings */
        $clientSettings = $client->getClientSettings();
        $mfpLink = false;
        if($clientSettings && ($clientSettings->getMfpUrl() ||$clientSettings->getMfpAccessToken())) {
            $mfpLink = true;
        }

        $unreadClientMessagesCount = $user->unreadMessagesCount($client);

        return $this->render('@Progress/Default/index.html.twig', compact(
            'client',
            'progress',
            'charts',
            'kpi',
            'clientStatus',
            'imageUrl',
            'kcalNeed',
            'mfpLink',
            'unreadClientMessagesCount'
        ));
    }
}
