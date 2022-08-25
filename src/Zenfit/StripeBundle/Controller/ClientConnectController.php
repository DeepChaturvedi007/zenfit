<?php

namespace Zenfit\StripeBundle\Controller;

use AppBundle\Controller\Controller;
use AppBundle\Security\CurrentUserFetcher;
use AppBundle\Services\StripeConnectService;
use AppBundle\Services\ErrorHandlerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use AppBundle\Repository\ClientRepository;

class ClientConnectController extends Controller
{
    public function __construct(
        EntityManagerInterface $em,
        private StripeConnectService $stripeConnectService,
        private ErrorHandlerService $errorHandlerService,
        private CurrentUserFetcher $currentUserFetcher,
        TokenStorageInterface $tokenStorage,
        private ClientRepository $clientRepository
    ) {
        parent::__construct($em, $tokenStorage);
    }

    public function unsubscribeClientAction(Request $request): JsonResponse
    {
        $user = $this->currentUserFetcher->getCurrentUser();

        $client = $this
            ->clientRepository
            ->find($request->request->get('client'));

        if ($client === null) {
            throw new BadRequestHttpException('No client found');
        }

        if (!$this->clientBelongsToUser($client, $user)) {
            throw new AccessDeniedHttpException("You don't have access to this client");
        }

        try {
            $userStripe = $client->getUser()->getUserStripe();

            if ($userStripe !== null) {
                $this
                    ->stripeConnectService
                    ->setClient($client)
                    ->setUserStripe($userStripe)
                    ->unsubscribeClient();
            }

            return new JsonResponse(['canceled' => true, 'client' => $client->getId()]);
        } catch (\Throwable $e) {
            $this->errorHandlerService->captureException($e);
            return new JsonResponse(['canceled' => false], 422);
        }
    }

    public function pauseSubscriptionClientAction(Request $request): JsonResponse
    {
        $user = $this->currentUserFetcher->getCurrentUser();

        $client = $this
            ->clientRepository
            ->find($request->request->get('client'));

        if ($client === null) {
            throw new BadRequestHttpException('No client found');
        }

        if (!$this->clientBelongsToUser($client, $user)) {
            throw new AccessDeniedHttpException("You don't have access to this client");
        }

        $trialEnd = (string) $request->request->get('trialEnd');
        $pause = $request->request->has('pause');
        $userStripe = $client->getUser()->getUserStripe();

        try {
            if ($userStripe === null) {
                throw new \RuntimeException('No UserStripe entity');
            }

            $this
                ->stripeConnectService
                ->setClient($client)
                ->setUserStripe($userStripe)
                ->updatePaymentDate($trialEnd, $pause);

            return new JsonResponse([
                'client' => $client->getId(),
                'pause' => $pause,
                'trialEnd' => $trialEnd
            ]);
        } catch (\Throwable $e) {
            $this->errorHandlerService->captureException($e);
            return new JsonResponse(['paused' => false, 'error' => $e->getMessage()], 422);
        }
    }

    public function refundClientAction(Request $request): JsonResponse
    {
        $user = $this->currentUserFetcher->getCurrentUser();

        $client = $this
            ->clientRepository
            ->find($request->request->get('client'));

        if ($client === null) {
            throw new BadRequestHttpException('No client found');
        }

        if (!$this->clientBelongsToUser($client, $user)) {
            throw new AccessDeniedHttpException("You don't have access to this client");
        }

        $chargeId = (string) $request->request->get('chargeId');
        $userStripe = $client->getUser()->getUserStripe();

        try {
            if ($userStripe === null) {
                throw new \RuntimeException('No UserStripe entity');
            }

            $this
                ->stripeConnectService
                ->setClient($client)
                ->setUserStripe($userStripe)
                ->refundClient($chargeId);

            return new JsonResponse([
                'refunded' => true,
                'client' => $client->getId()
            ]);
        } catch (\Throwable $e) {
            $this->errorHandlerService->captureException($e);
            return new JsonResponse(['refunded' => false, 'error' => $e->getMessage()], 422);
        }
    }

}
