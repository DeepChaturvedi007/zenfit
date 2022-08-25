<?php

namespace ClientBundle\Controller;

use AppBundle\Controller\Controller;
use AppBundle\Entity\User;
use AppBundle\Repository\ClientRepository;
use AppBundle\Services\ClientService;
use ChatBundle\Repository\MessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @Route("/api/clients")
 */
class ClientsController extends Controller
{
    private ClientService $clientService;
    private ClientRepository $clientRepository;
    private MessageRepository $messageRepository;

    public function __construct(
        EntityManagerInterface $em,
        ClientRepository $clientRepository,
        MessageRepository $messageRepository,
        ClientService $clientService,
        TokenStorageInterface $tokenStorage
    ) {
        $this->clientService = $clientService;
        $this->clientRepository = $clientRepository;
        $this->messageRepository = $messageRepository;

        parent::__construct($em, $tokenStorage);
    }

    /**
     * @Route("")
     * @Method({"GET"})
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws \Exception
     */
    public function getClientsAction(Request $request)
    {
        $currentUser = $this->getUser();
        if (!$currentUser instanceof User) {
            throw new AccessDeniedHttpException('Only logged in user can access this route');
        }

        // since we are not using session further, save it as soon as possible to unlock the session for further requests
        // e.g. client counting is called almost simultaneously
        $request->getSession()->save();

        $userToFetchClientsFrom = $currentUser;
        if (!$userToFetchClientsFrom instanceof User) {
            throw new AccessDeniedHttpException();
        }

        if ($currentUser->isAssistant()) {
            $userToFetchClientsFrom = $currentUser->getGymAdmin();

            $tags = [$currentUser->getFirstName()];
        } else {
            $tags = collect($request->get('tags', []))->all();
        }

        $status = $request->get('status');
        $q = $request->get('q', '');
        $offset = $request->get('offset');
        $limit = $request->get('limit');
        $active = $status === 'active' ? 1 : 0;
        $filter = $request->get('filter');

        $sortColumn = $request->get('sortColumn');
        $sortOrder = $request->get('sortOrder');

        $filters = $this->getFilters($filter);

        try {
            $clients = $this
                ->clientRepository
                ->getClientsByFilters(
                    $userToFetchClientsFrom,
                    $active,
                    $q,
                    $offset,
                    $limit,
                    $filters,
                    $tags,
                    false,
                    $sortColumn,
                    $sortOrder
                );

            $clientsStats = $this->clientRepository->getStatsByClients($clients);

            $clients = $this
                ->clientService
                ->collectClients($clients, $clientsStats);

            return new JsonResponse($clients);

        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * @Route("/count")
     * @Method({"GET"})
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getClientCountAction(Request $request)
    {
        $currentUser = $this->getUser();
        if (!$currentUser instanceof User) {
            throw new AccessDeniedHttpException('Only logged in user can access this route');
        }

        $status = $request->get('status');
        $q = $request->get('q', '');
        $active = $status === 'active' ? 1 : 0;

        $userToFetchClientsFrom = $currentUser;
        if (!$userToFetchClientsFrom instanceof User) {
            throw new AccessDeniedHttpException();
        }

        if ($currentUser->isAssistant()) {
            $userToFetchClientsFrom = $currentUser->getGymAdmin();

            $tags = [$currentUser->getFirstName()];
        } else {
            $tags = collect($request->get('tags', []))->all();
        }

        $repo = $this->clientRepository;

        $count = collect($this->getFilters())
            ->map(function ($events) use ($repo, $userToFetchClientsFrom, $active, $q, $tags) {
                return $repo->getClientsByFilters($userToFetchClientsFrom, $active, $q, null, null, $events, $tags, true);
            })->toArray();

        return new JsonResponse($count);
    }

    /**
     * @Route("/delete")
     * @Method({"POST"})
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteAction(Request $request)
    {
        try {
            $service = $this->clientService;
            $service->deleteClients($request->get('clients'));
            return new JsonResponse('OK');
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * @Route("/deactivate")
     * @Method({"POST"})
     * @param Request $request
     * @return JsonResponse
     */
    public function deactivateAction(Request $request)
    {
        try {
            $service = $this->clientService;
            $service->deactivateClients($request->get('clients'));
            return new JsonResponse('OK');
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => $e->getMessage()
            ], 422);
        }
    }

    private function getFilters($filter = null)
    {
        $service = $this->clientService;
        if ($filter) {
            return $service->getClientFilters($filter);
        } else {
            return [
                'pending' => $service->getClientFilters('pending'),
                'all' => $service->getClientFilters('all'),
                'need-plans' => $service->getClientFilters('need-plans'),
                'missing-checkin' => $service->getClientFilters('missing-checkin'),
                'progress' => $service->getClientFilters('progress'),
                'unanswered' => $service->getClientFilters('unanswered'),
                'old-chats' => $service->getClientFilters('old-chats'),
                'ending' => $service->getClientFilters('ending'),
                'payments' => $service->getClientFilters('payments'),
                'custom' => $service->getClientFilters('custom'),
                'other' => $service->getClientFilters('other')
            ];
        }
    }
}
