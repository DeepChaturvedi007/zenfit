<?php declare(strict_types=1);

namespace TrainerBundle\Controller;

use AppBundle\Controller\Controller;
use AppBundle\Entity\Queue;
use AppBundle\EventListener\RegistrationCompletedListener;
use AppBundle\Repository\LanguageRepository;
use AppBundle\Services\QueueService;
use AppBundle\Services\TrackingService;
use AppBundle\Services\TrainerAssetsService;
use Doctrine\ORM\EntityManagerInterface;
use AppBundle\Repository\QueueRepository;
use AppBundle\Repository\UserRepository;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use Gesdinet\JWTRefreshTokenBundle\Generator\RefreshTokenGeneratorInterface;
use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenManagerInterface;
use GymBundle\Services\TrainerService;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use AppBundle\Services\SettingsService;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[Route("/auth/api")]
class AuthApiController extends Controller
{
    public function __construct(
        TokenStorageInterface $tokenStorage,
        private EntityManagerInterface $em,
        private int $refreshTokenTTL,
        private QueueService $queueService,
        private UrlGeneratorInterface $urlGenerator,
        private QueueRepository $queueRepository,
        private UserRepository $userRepository,
        private SettingsService $settingsService,
        private TrainerService $trainerService,
        private TrackingService $trackingService,
        private JWTTokenManagerInterface $JWTTokenManager,
        private RefreshTokenManagerInterface $refreshTokenManager,
        private RefreshTokenGeneratorInterface $refreshTokenGenerator,
        private LanguageRepository $languageRepository,
        private TrainerAssetsService $trainerAssetsService,
        private EventDispatcherInterface $eventDispatcher,
        private string $appHostname
    ) {
        parent::__construct($em, $tokenStorage);
    }

    #[Route("/sign-up", methods: ["POST"])]
    public function signup(Request $request): JsonResponse
    {
        try {
            $body = $this->requestInput($request);

            $phone = null;
            if (array_key_exists('phone', $body) && $body['phone'] !== null) {
                $phone = (string) $body['phone'];
            }

            $language = null;
            if (array_key_exists('locale', $body)) {
                $language = $this
                    ->languageRepository
                    ->findByLocale($body['locale']);
            }

            if ($language === null) {
                throw new \RuntimeException('No language found');
            }

            $user = $this
                ->trainerService
                ->create((string) $body['name'], (string) $body['email'], (string) $body['password'], $phone, null, $language, false);

            if (array_key_exists('businessName', $body)) {
                $this
                    ->trainerAssetsService
                    ->getUserSettings($user)
                    ->setCompanyName($body['businessName']);
            }

            $this->em->flush();

            $response = new JsonResponse('OK');

            $this->eventDispatcher->dispatch(new FilterUserResponseEvent($user, $request, $response), RegistrationCompletedListener::EVENT_NAME);

            $this
                ->trackingService
                ->fireEvent('signup', ['locale' => $language->getLocale()]);

            $accessToken = $this->JWTTokenManager->create($user);
            $response = new JsonResponse('OK');
            $response->headers->setCookie(Cookie::create('BEARER', $accessToken));

            $refreshToken = $this->refreshTokenGenerator->createForUserWithTtl($user, $this->refreshTokenTTL);
            $this->refreshTokenManager->save($refreshToken);

            $response->headers->setCookie(Cookie::create('REFRESH_TOKEN', $refreshToken->getRefreshToken()));

            return $response;
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 422);
        }
    }

    #[Route("/login", methods: ["POST"])]
    public function login(Request $request): JsonResponse
    {
        try {
            $body = $this->requestInput($request);
            $user = $this
                ->settingsService
                ->login($body['email'], $body['password']);

            if ($user === null) {
                throw new HttpException(422, 'No user with that email or password combination.');
            }

            return new JsonResponse(['token' => $user->getInteractiveToken()]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 422);
        }
    }

    #[Route("/forgot-password", methods: ["POST"])]
    public function forgotPassword(Request $request): JsonResponse
    {
        try {
            $body = $this->requestInput($request);
            $user = $this
                ->userRepository
                ->findOneBy([
                    'email' => $body['email']
                ]);

            if ($user === null) {
                throw new \RuntimeException('No user with that email');
            }

            $previousQueues = collect(
                $this
                    ->queueRepository
                    ->findAllByUser(
                        $user,
                        [Queue::TYPE_MESSAGE_TO_TRAINER],
                        [Queue::STATUS_SENT, Queue::STATUS_PENDING]
                    )
            );

            if ($previousQueues->isNotEmpty()) {
                $previousQueues->each(function (Queue $queue) {
                    $queue->setStatus(Queue::STATUS_CANCELED);
                });
                $this->getEm()->flush();
            }

            $datakey = $this
                ->queueService
                ->getRandomKey();

            $url =
                $this->appHostname .
                $this->urlGenerator->generate('authNewPassword', ['datakey' => $datakey]);

            $queue = $this
                ->queueService
                ->insertIntoEmailQueue(
                    $user->getEmail(),
                    $user->getEmailName(),
                    Queue::STATUS_PENDING,
                    Queue::TYPE_MESSAGE_TO_TRAINER,
                    null,
                    $datakey,
                    null,
                    'Password reset request for Zenfit',
                    null,
                    $user,
                    "You have requested a new password for Zenfit. <br />Click here to create a new one: <br /><br />$url<br /><br />If you did not request this, please ignore this email."
                );
        } catch (\Exception $e) {
            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse(['message' => 'We\'ll send you an email right away with a link to reset your password.']);
    }

    #[Route("/save-password", methods: ["POST"])]
    public function passwordSave(Request $request): JsonResponse
    {
        try {
            $body = $this->requestInput($request);
            $datakey = $body['datakey'];
            $password1 = $body['password1'];
            $password2 = $body['password2'];

            $queue = $this
                ->queueRepository
                ->findOneBy([
                    'type' => Queue::TYPE_MESSAGE_TO_TRAINER,
                    'datakey' => $datakey
                ]);

            if ($queue === null) {
                throw new \RuntimeException('No key found');
            }

            if ($queue->getUser() === null) {
                throw new \RuntimeException('No user found');
            }

            $this
                ->settingsService
                ->changePassword($queue->getUser(), $password1, $password2);

            $queue->setStatus(Queue::STATUS_CONFIRMED);
            $this->getEm()->flush();
        } catch (\Exception $e) {
            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse(['message' => 'Password saved.']);
    }
}
