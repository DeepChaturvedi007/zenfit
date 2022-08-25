<?php

namespace Zenfit\StripeBundle\Controller;

use AppBundle\Controller\Controller;
use AppBundle\Security\CurrentUserFetcher;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use AppBundle\Repository\ClientRepository;
use AppBundle\Repository\PaymentsLogRepository;

class ApiController extends Controller
{
    public function __construct(
        EntityManagerInterface $em,
        TokenStorageInterface $tokenStorage,
        private ClientRepository $clientRepository,
        private PaymentsLogRepository $paymentsLogRepository,
        private CurrentUserFetcher $currentUserFetcher,
    ) {

        parent::__construct($em, $tokenStorage);
    }

    public function getClientPaymentsLogAction(Request $request): JsonResponse
    {
        $user = $this->currentUserFetcher->getCurrentUser();

        $client = $this
            ->clientRepository
            ->find($request->query->get('client'));

        if ($client === null) {
            throw new BadRequestHttpException('No client found');
        }

        if (!$this->clientBelongsToUser($client, $user)) {
            throw new AccessDeniedHttpException("You don't have access to this client");
        }

        $paymentsLog = $this
            ->paymentsLogRepository
            ->findByClient($client);

        return new JsonResponse($paymentsLog);
    }

}
