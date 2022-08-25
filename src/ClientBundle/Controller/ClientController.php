<?php

namespace ClientBundle\Controller;

use AppBundle\Controller\Controller;
use AppBundle\Entity\Client;
use AppBundle\Entity\ClientReminder;
use AppBundle\Entity\User;
use AppBundle\Services\ClientService;
use AppBundle\Services\MyFitnessPalService;
use AppBundle\Services\QueueService;
use AppBundle\Services\StripeConnectService;
use AppBundle\Services\ValidationService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Exception;
use ReactApiBundle\Services\AuthService;
use ReactApiBundle\Services\TokenService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use AppBundle\Entity\Queue;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Throwable;

/**
 * @Route("/client")
 */
class ClientController extends Controller
{
    private QueueService $queueService;
    private ClientService $clientService;
    private StripeConnectService $stripeConnectService;
    private MyFitnessPalService $myFitnessPalService;
    private ValidationService $validationService;
    private AuthService $authService;
    private string $stripePublishableKey;
    private string $appHostname;
    private SessionInterface $session;
    private UrlGeneratorInterface $urlGenerator;
    private TokenService $tokenService;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        QueueService $queueService,
        EntityManagerInterface $em,
        SessionInterface $session,
        TokenService $tokenService,
        UrlGeneratorInterface $urlGenerator,
        EventDispatcherInterface $eventDispatcher,
        ValidationService $validationService,
        ClientService $clientService,
        StripeConnectService $stripeConnectService,
        AuthService $authService,
        string $stripePublishableKey,
        string $appHostname,
        MyFitnessPalService $myFitnessPalService,
        TokenStorageInterface $tokenStorage
    ) {
        $this->queueService = $queueService;
        $this->stripeConnectService = $stripeConnectService;
        $this->clientService = $clientService;
        $this->validationService = $validationService;
        $this->myFitnessPalService = $myFitnessPalService;
        $this->authService = $authService;
        $this->appHostname = $appHostname;
        $this->session = $session;
        $this->eventDispatcher = $eventDispatcher;
        $this->tokenService = $tokenService;
        $this->urlGenerator = $urlGenerator;
        $this->stripePublishableKey = $stripePublishableKey;

        parent::__construct($em, $tokenStorage);
    }

    /**
     * @Route("/login/{interactiveToken}", name="clientLogin")
     * @Method("GET")
     *
     * @param Request $request
     * @param string $interactiveToken
     *
     * @return Response
     *
     * @throws Throwable
     */
    public function loginAction(Request $request, $interactiveToken = null)
    {
        try {
            $this->verifyToken($request);
            return new RedirectResponse('/client/settings', RedirectResponse::HTTP_FOUND);
        } catch (\Exception $e) {
        }

        if ($this->attemptByInteractiveToken($interactiveToken)) {
            return new RedirectResponse('/client/settings', RedirectResponse::HTTP_FOUND);
        }

        return $this->render('@Client/Default/login.html.twig');
    }

    /**
     * @Route("/login", name="clientSendLogin")
     * @Method("POST")
     *
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function loginSendAction(Request $request)
    {
        $session = $this->session;
        $email = $request->request->get('email');
        $password = $request->request->get('password');

        $url = '/client/settings';

        try {
            $client = $this
                ->authService
                ->login($email, $password);

            $token = $this
                ->tokenService
                ->createToken($client);

            $session
                ->set('client.token', $token);
        } catch (Exception $e) {
            $session
                ->getFlashBag()
                ->add('error', $e->getMessage());

            $url = '/client/login';
        }

        return new RedirectResponse($url);
    }

    /**
     * @Route("/settings", name="clientSettings")
     * @Method("GET")
     *
     * @param Request $request
     * @param SessionInterface $session
     *
     * @return Response
     *
     * @throws Throwable
     */
    public function settingsAction(Request $request, SessionInterface $session)
    {
        list ($client, $token) = $this->authClient($request);

        /** @var ?Client $client */
        $clientId = null;
        if ($client !== null) {
            $clientId = $client->getId();
        }
        if ($client === null || $clientId === null) {
            $this->session->getFlashBag()->add('error', 'Invalid token.');
            return new RedirectResponse('/client/login', RedirectResponse::HTTP_FOUND);
        }

        $userStripe = $client->getUser()->getUserStripe();

        if ($userStripe && $request->query->has('session_id')) {
            //client is updating his card
            $this
                ->stripeConnectService
                ->setUserStripe($userStripe)
                ->setClient($client)
                ->updateClientCard($request->query->get('session_id'));
        }

        $mfpAuthUrl = $this
            ->myFitnessPalService
            ->getAuthUrl($clientId);

        $isIntegratedWithMFP = $this
            ->myFitnessPalService
            ->isClientIntegratedWithMFP($client);

        $stripeAccount = null;
        $clientStripe = $client->getClientStripe();
        if ($clientStripe && !$clientStripe->getCanceled() && $userStripe) {
            $stripeAccount = $userStripe->getStripeUserId();
        }

        return $this->render('@Client/Default/settings.html.twig', [
            'client' => $client,
            'token' => $token->hash,
            'stripeKey' => $this->stripePublishableKey,
            'mfpAuthUrl' => $mfpAuthUrl,
            'isIntegratedWithMFP' => $isIntegratedWithMFP,
            'stripeAccount' => $stripeAccount
        ]);
    }

    /**
     * @Route("/create-session", name="createSession")
     * @Method("POST")
     */
    public function createSessionAction(Request $request)
    {
        $client = $this
            ->getEm()
            ->getRepository(Client::class)
            ->find($request->request->get('client'));

        if ($client === null) {
            throw new NotFoundHttpException('Client not found');
        }

        $userStripe = $client->getUser()->getUserStripe();
        if ($userStripe === null) {
            throw new \RuntimeException('No UserStripe object');
        }
        $session = $this
            ->stripeConnectService
            ->setUserStripe($userStripe)
            ->setClient($client)
            ->createSession();

        return new JsonResponse(['sessionId' => $session->id]);
    }


    /**
     * @Route("/settings", name="clientSendSettings")
     * @Method("POST")
     *
     * @param Request $request
     *
     * @return Response
     *
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws Throwable
     */
    public function settingsSendAction(Request $request)
    {
        list ($client) = $this->authClient($request);

        if (!$client) {
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse([
                    'message' => 'Invalid Token.',
                ], JsonResponse::HTTP_UNAUTHORIZED);
            }

            $this
                ->session
                ->getFlashBag()
                ->add('error', 'Invalid token.');

            return new RedirectResponse('/client/login', RedirectResponse::HTTP_FOUND);
        }

        $name = $request->request->get('name');
        $email = $request->request->get('email');
        $password = $request->request->get('password');
        $password2 = $request->request->get('password2');
        $currentPassword = $request->request->get('currentPassword');
        $receiveEmails = $request->request->get('receiveEmails', false);

        $validationService = $this->validationService;
        $clientService = $this->clientService;

        try {
            $client->setAcceptEmailNotifications($receiveEmails);

            if ($password) {
                $validationService->checkClientPassword($currentPassword, $client->getPassword());
                $validationService->passwordValidation($password, $password2);
                $client->setPassword($password);
            }

            $validationService->checkEmptyString($name);

            if ($client->getName() != $name) {
                $client->setName($name);
            }

            $validationService->checkEmptyString($email);
            $validationService->checkEmail($email);

            if ($client->getEmail() != $email) {
                $clientService->clientEmailExist($email);
                $client->setEmail($email);
            }
        } catch (Exception $e) {
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse([
                    'message' => $e->getMessage(),
                ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
            }

            $this
                ->session
                ->getFlashBag()
                ->add('error', $e->getMessage());
        }

        $this->getEm()->flush();

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse([
                'success' => true,
                'message' => 'Settings saved'
            ]);
        }

        $this
            ->session
            ->getFlashBag()
            ->add('success', 'Settings saved.');

        return new RedirectResponse('/client/settings', RedirectResponse::HTTP_FOUND);
    }

    /**
     * @Route("/logout", name="clientLogout")
     * @Method("GET")
     *
     * @return RedirectResponse
     */
    public function logoutAction()
    {
        $this->session->remove('client.token');
        return new RedirectResponse('/client/login', RedirectResponse::HTTP_FOUND);
    }

    /**
     * @Route("/forgot-password", name="clientForgotPassword")
     * @Method("GET")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function forgotPasswordAction(Request $request)
    {
        return $this->render('@Client/Default/forgotPassword.html.twig');
    }

    /**
     * @Route("/confirm-unsubscription/{datakey}", name="clientConfirmUnsubscription")
     * @Method("GET")
     *
     * @param String $datakey
     *
     * @return Response
     */
    public function confirmUnsubscriptionAction($datakey)
    {
        $queue = $this
            ->getEm()
            ->getRepository(Queue::class)
            ->findOneBy([
                'datakey' => $datakey,
            ]);

        if ($queue === null) {
            throw new NotFoundHttpException('No key found');
        }

        return $this->render('@Client/Default/confirmUnsubscription.html.twig', [
            'client' => $queue->getClient(),
        ]);
    }

    /**
     * @Route("/request-unsubscribe", name="clientRequestUnsubscribe")
     * @Method("POST")
     *
     * @param Request $request
     *
     * @return Response
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function requestUnsubscribeAction(Request $request)
    {
        $em = $this->getEm();

        $client = $em
            ->getRepository(Client::class)
            ->find($request->request->get('client'));

        if ($client === null) {
            throw new NotFoundHttpException('Client not found');
        }

        $clientName = $client->getName();

        $queueService = $this->queueService;
        $datakey = $queueService->getRandomKey();

        $url =
            $this->appHostname .
            $this->urlGenerator->generate('clientConfirmUnsubscription', ['datakey' => $datakey]);

        $msg = "$clientName wants to unsubscribe from his/her current subscription.
            <br /><br />Click <a href=$url>here</a> to confirm, or simply ignore this email to reject.";

        /**
         * @var User $user
         */
        $user = $client->getUser();

        $queue = $queueService->insertIntoEmailQueue(
            $user->getEmail(),
            $user->getEmailName(),
            Queue::STATUS_PENDING,
            Queue::TYPE_MESSAGE_TO_TRAINER,
            $client,
            $datakey,
            null,
            'A client wants to unsubscribe',
            null,
            $user
        );

        $queue->setMessage($msg);

        $title = $request->get('title');

        $cm = (new ClientReminder())
            ->setClient($client)
            ->setDueDate(new \DateTime('now'))
            ->setTitle('Unsubscribe request');

        $em->persist($cm);
        $em->flush();

        return new JsonResponse(['success' => true]);
    }

    protected function verifyToken(Request $request): object
    {
        $tokenService = $this->tokenService;
        $tokenHash = $tokenService->getTokenFromHeaders($request) ?? $this->session->get('client.token');

        $token = $tokenService->validateToken($tokenHash);

        throw_unless($token, new HttpException(Response::HTTP_UNAUTHORIZED, 'No client found.'));

        $payload = $tokenService->getTokenPayload($token);
        $payload->hash = $tokenHash;

        return $payload;
    }

    /**
     * @param Request $request
     *
     * @return array[?Client, Token]
     *
     * @throws Throwable
     */
    protected function authClient(Request $request): array
    {
        try {
            $token = $this->verifyToken($request);
        } catch (Exception) {
            return [];
        }

        $em = $this->getEm();

        $client = null;
        if (property_exists($token, 'client')) {
            /** @var ?Client $client */
            $client = $em
                ->getRepository(Client::class)
                ->find($token->client);
        }

        return [$client, $token];
    }

    /**
     * @param string $token
     *
     * @return bool
     */
    protected function attemptByInteractiveToken($token)
    {
        if ($token) {
            /** @var ?Client $client */
            $client = $this
                ->getEm()
                ->getRepository(Client::class)
                ->findOneBy(['token' => $token]);

            if ($client) {
                $token = $this
                    ->tokenService
                    ->createToken($client);

                $this->session->set('client.token', $token);

                return true;
            }
        }

        return false;
    }

    /**
     * @Route("/delete/{client}", name="delete_client")
     */
    public function deleteClient(Client $client, Request $request): Response
    {
        /** @var ?Client $authedClient */
        [$authedClient] = $this->authClient($request);

        if (!$authedClient) {
            $this->addFlash('error', 'Unauthorized client');
            return new RedirectResponse('/client/login');
        }

        if ($authedClient->getId() !== $client->getId()) {
            $this->addFlash('error', 'Currently logged in client can not delete this client');
            return new RedirectResponse('/client/settings');
        }

        $this->clientService->anonymizeClient($client);

        $this->session->remove('client.token');

        $this->getEm()->flush();

        $this->addFlash('success', 'Account successfully deleted');

        return new RedirectResponse('/client/login');
    }
}
