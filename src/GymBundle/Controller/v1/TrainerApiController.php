<?php declare(strict_types=1);

namespace GymBundle\Controller\v1;

use AppBundle\Entity\Client;
use AppBundle\Entity\MasterMealPlan;
use AppBundle\Entity\User;
use AppBundle\Entity\WorkoutPlan;
use AppBundle\EventListener\RegistrationCompletedListener;
use ChatBundle\Entity\Conversation;
use Doctrine\ORM\EntityManagerInterface;
use GymBundle\Controller\Controller as Controller;
use GymBundle\Services\TrainerService;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\PasswordHasher\Hasher\MigratingPasswordHasher;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Component\Routing\Annotation\Route;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Model\UserManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use AppBundle\Services\SettingsService;

/**
 * @Route("/v1/api/trainer")
 */
class TrainerApiController extends Controller
{
    private EventDispatcherInterface $eventDispatcher;
    private PasswordHasherFactoryInterface $formFactory;
    private UserManagerInterface $userManager;
    private TrainerService $trainerService;
    private string $appHostname;
    private UrlGeneratorInterface $urlGenerator;
    private SettingsService $settingsService;

    public function __construct(
        string $appHostname,
        UrlGeneratorInterface $urlGenerator,
        TrainerService $trainerService,
        EntityManagerInterface $em,
        EventDispatcherInterface $eventDispatcher,
        PasswordHasherFactoryInterface $formFactory,
        UserManagerInterface $userManager,
        SettingsService $settingsService
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->formFactory = $formFactory;
        $this->trainerService = $trainerService;
        $this->userManager = $userManager;
        $this->urlGenerator = $urlGenerator;
        $this->appHostname = $appHostname;
        $this->settingsService = $settingsService;

        parent::__construct($em);
    }

    /**
     * @Route("/auth/signup", name="trainer_signup_api", methods={"POST"})
     */
    public function trainerSignupAction(Request $request): JsonResponse
    {
        $name = $request->request->get('name');
        $email = $request->request->get('email');
        $phone = $request->request->get('phone');
        $password = $request->request->get('password');

        try {
            $user = $this->trainerService
                ->create($name, $email, $password, $phone);
        } catch (HttpException $e) {
            return new JsonResponse([
              'error' => $e->getMessage()
            ], $e->getStatusCode());
        }

        $url = $this->appHostname .
          $this->urlGenerator->generate('interactiveLogin', [
            'token' => $user->getInteractiveToken(),
            'route' => 'dashboardOverview'
          ]);

        $response = new RedirectResponse($url);
        $this->eventDispatcher->dispatch(new FilterUserResponseEvent($user, $request, $response), RegistrationCompletedListener::EVENT_NAME);

        return new JsonResponse(['redirect' => $url]);
    }

    /**
     * @Route("/auth/login", methods={"POST"})
     */
    public function trainerLoginAction(Request $request): JsonResponse
    {
        try {
            $body = $this->requestInput($request);
            $user = $this
                ->settingsService
                ->login($body->email, $body->password);

            if ($user === null) {
                throw new HttpException(422, 'No user with that email or password combination.');
            }

            return new JsonResponse(['token' => $user->getInteractiveToken()]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * @Route("/move-client", methods={"POST"})
     */
    public function moveClientAction(Request $request): JsonResponse
    {
        try {
            $body = $this->requestInput($request);
            $em = $this->getEm();
            $client = $em
                ->getRepository(Client::class)
                ->find($body->client);

            if ($client === null) {
                throw new NotFoundHttpException('Client not found');
            }

            //move client to new trainer
            $user = $em
                ->getRepository(User::class)
                ->find($body->user);

            if ($user === null) {
                throw new NotFoundHttpException('User not found');
            }

            $client->setUser($user);

            //find conversations by client
            $conversation = $em
                ->getRepository(Conversation::class)
                ->findByClient($client);

            if ($conversation) {
                $conversation->setUser($user);
            }

            //find workout plans by client
            $workoutPlans = $em
                ->getRepository(WorkoutPlan::class)
                ->getPlanByClient($client);

            foreach ($workoutPlans as $wp) {
                $wp->setUser($user);
            }

            //find meal plans by client
            $mealPlans = $em
                ->getRepository(MasterMealPlan::class)
                ->getByClient($client);

            foreach ($mealPlans as $mp) {
                $mp->setUser($user);
            }

            $em->flush();

            return new JsonResponse(['msg' => 'Client was moved.']);
        } catch (\Exception $e) {
            return new JsonResponse([
              'error' => 'A server error occured.',
              'msg' => $e->getMessage()
            ], 422);
        }
    }

}
