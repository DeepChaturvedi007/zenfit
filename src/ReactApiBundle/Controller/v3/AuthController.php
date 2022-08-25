<?php

namespace ReactApiBundle\Controller\v3;

use AppBundle\Entity\Queue;
use AppBundle\Entity\Event;
use AppBundle\Entity\Client;
use AppBundle\Services\PusherService;
use AppBundle\Services\QueueService;
use ChatBundle\Services\ChatService;
use Doctrine\ORM\EntityManagerInterface;
use ReactApiBundle\Services\AuthService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use ReactApiBundle\Controller\Controller as sfController;
use AppBundle\Event\ClientMadeChangesEvent;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use AppBundle\Repository\ClientRepository;

/**
 * @Route("/auth")
 */
class AuthController extends sfController
{
    private QueueService $queueService;
    private PusherService $pusherService;
    private ChatService $chatService;
    private EventDispatcherInterface $eventDispatcher;
    private AuthService $authService;
    private ClientRepository $clientRepository;
    private string $appHostname;

    public function __construct(
        AuthService $authService,
        EventDispatcherInterface $eventDispatcher,
        ChatService $chatService,
        PusherService $pusherService,
        QueueService $queueService,
        EntityManagerInterface $em,
        ClientRepository $clientRepository,
        string $appHostname
    ) {
        $this->appHostname = $appHostname;
        $this->queueService = $queueService;
        $this->chatService = $chatService;
        $this->pusherService = $pusherService;
        $this->eventDispatcher = $eventDispatcher;
        $this->authService = $authService;
        $this->clientRepository = $clientRepository;

        parent::__construct($em, $clientRepository);
    }

    /**
     * @Route("/login", name="react-api-client-login-v3", methods={"POST"})
     */
    public function loginAction(Request $request): JsonResponse
    {
        $input = $this->requestInput($request);

        try {
            $client = $this->authService->login($input->email, $input->password);
            return new JsonResponse($this->authService->getClientData($client));
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => $e->getMessage(),
            ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * @Route("/me", methods={"GET"})
     */
    public function meAction(Request $request): JsonResponse
    {
        $client = $this->requestClientByToken($request);

        if (!$client) {
            return new JsonResponse(null, JsonResponse::HTTP_UNAUTHORIZED);
        }

        if (!$client->getAccessApp()) {
            return new JsonResponse([
                'message' => 'Your login is correct, but you have been disabled access to the app. Contact your coach.',
            ], JsonResponse::HTTP_UNAUTHORIZED);
        }

        return new JsonResponse($this->authService->getClientData($client));
    }

    /**
     * @Route("/forgot-password", name="react-api-forgot-password-v3", methods={"POST"})
     */
    public function forgotPasswordAction(Request $request): JsonResponse
    {
        $input = $this->requestInput($request);
        $em = $this->em;
        $client = $this
            ->clientRepository
            ->findOneBy([
                'email' => $input->email,
            ], ['id' => 'DESC']);

        if (!$client) {
            return new JsonResponse([
                'reason' => 'A client with this email does not exist',
            ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (!$client->getAccessApp()) {
            return new JsonResponse([
                'message' => 'Your access to the app has been disabled, so you cannot retrieve your password. Contact your coach.',
            ], JsonResponse::HTTP_UNAUTHORIZED);
        }

        $queueService = $this->queueService;
        $queue = $queueService->createClientCreationEmailQueueEntity($client);
        $url = $this->appHostname .
            $this->generateUrl('clientActivation', array(
                    'datakey' => $queue->getDatakey()
                )
            );

        $userApp = $client->getUser()->getUserApp();
        $appName = $userApp ? $userApp->getTitle() : 'Zenfit';

        $message =
            "<p>You have requested a new password for the $appName App.
        Click this link: <a href=$url>$url</a> to do so.</p>
        <p>If you have not requested a new password, please ignore this email.</p>";
        $subject = "Request Password Reset - $appName";

        $queue
            ->setMessage($message)
            ->setSubject($subject)
            ->setStatus(Queue::STATUS_PENDING)
            ->setEmail($client->getEmail());

        $em->flush();

        return new JsonResponse([
            'reason' => 'A password reset email will be sent.',
        ]);
    }
}
