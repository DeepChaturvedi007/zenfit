<?php

namespace WorkoutPlanBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Entity\Workout;
use AppBundle\Services\WorkoutPlanService;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use WorkoutPlanBundle\Transformer\WorkoutDayTransformer;
use AppBundle\Repository\WorkoutPlanRepository;
use AppBundle\Entity\WorkoutPlan;
use AppBundle\Entity\WorkoutDay;
use AppBundle\Entity\Client;

/**
 * @Route("/api/workout")
 */
class ApiController extends Controller
{
    private WorkoutPlanService $workoutPlanService;
    private WorkoutPlanRepository $workoutPlanRepository;

    public function __construct(
        WorkoutPlanService $workoutPlanService,
        EntityManagerInterface $em,
        TokenStorageInterface $tokenStorage,
        WorkoutPlanRepository $workoutPlanRepository
    ) {
        $this->workoutPlanService = $workoutPlanService;
        $this->workoutPlanRepository = $workoutPlanRepository;

        parent::__construct($em, $tokenStorage);
    }

    /**
     * @Route("/client/plans/{client}", methods={"GET"})
     */
    public function getWorkoutPlans(Client $client): JsonResponse
    {
        $currentUser = $this->getUser();
        if (!$currentUser instanceof User) {
            throw new AccessDeniedHttpException();
        }

        $user = $currentUser->isAssistant() ? $currentUser->getGymAdmin() : $currentUser;

        if (!$this->clientBelongsToUser($client)) {
            throw new AccessDeniedHttpException();
        }

        $plans = $this->workoutPlanRepository->getAllByClientAndUser($client, $user);
        return new JsonResponse($plans);
    }

    /**
     * @Route("/template/plans", methods={"GET"})
     */
    public function getTemplateWorkoutPlans(Request $request): JsonResponse
    {
        $currentUser = $this->getUser();
        if (!$currentUser instanceof User) {
            throw new AccessDeniedHttpException();
        }

        $user = $currentUser->isAssistant() ? $currentUser->getGymAdmin() : $currentUser;

        $location = $request->get('location');
        if ($location !== null) {
            $location = (int) $location;
        }
        $workoutsPerWeek = $request->get('workoutsPerWeek');
        if ($workoutsPerWeek !== null) {
            $workoutsPerWeek = (int) $workoutsPerWeek;
        }
        $gender = $request->get('gender');
        if ($gender !== null) {
            $gender = (int) $gender;
        }
        $level = $request->get('level');
        if ($level !== null) {
            $level = (int) $level;
        }

        $plans = $this->workoutPlanRepository->getAllByUser($user, true, $location, $workoutsPerWeek, $gender, $level);
        return new JsonResponse($plans);
    }

    /**
     * @Method({"GET"})
     * @Route("/client/{plan}/days")
     * @param WorkoutPlan $plan
     * @return JsonResponse
     */
    public function plansAction(WorkoutPlan $plan)
    {
        $currentUser = $this->getUser();
        if (!$currentUser instanceof User) {
            throw new AccessDeniedHttpException();
        }
        $user = $currentUser->isAssistant() ? $currentUser->getGymAdmin() : $currentUser;

        $planClient = $plan->getClient();
        if ($planClient !== null && !$this->clientBelongsToUser($planClient)) {
            throw new AccessDeniedHttpException();
        }

        $em = $this->getEm();
        $dayRepo = $em->getRepository(WorkoutDay::class);
        $days = $dayRepo->findBy(
            ['workoutPlan' => $plan],
            ['order' => 'ASC']
        );
        $response = [];

        if (count($days) > 0) {
            $i = 1;
            /**
             * @var $day WorkoutDay
             */
            foreach ($days as $day) {
                $response[] = (new WorkoutDayTransformer($this->getEm()))->transform($day);
                $i++;
            }
        }

        return new JsonResponse($response);
    }

  	/**
  	 * @Route("/client/assign-plan/{template}", name="assign_plan", methods={"POST"})
  	 */
  	public function assignPlanToClients(WorkoutPlan $template, Request $request): JsonResponse
  	{
        $currentUser = $this->getUser();
        if (!$currentUser instanceof User) {
            throw new AccessDeniedHttpException();
        }
        $user = $currentUser->isAssistant() ? $currentUser->getGymAdmin() : $currentUser;

        try {
            $content = json_decode($request->getContent());
            $plans = $this
                ->workoutPlanService
                ->assignPlanToClients($template, $content->clientsIds); //TODO check clients belong to Trainer\Assistant
            return new JsonResponse($plans);
        } catch (\Exception $e) {
          return new JsonResponse(['err' => $e->getMessage()], 422);
        }
  	}

    /**
     * @param WorkoutDay $day
     * @return JsonResponse
     * @Route("/client/plan/day/{day}", name="apiClientWorkoutDay")
     * @Method({"GET"})
     */
    public function apiClientWorkoutDayAction(WorkoutDay $day)
    {
        $currentUser = $this->getUser();
        if (!$currentUser instanceof User) {
            throw new AccessDeniedHttpException();
        }

        $user = $currentUser->isAssistant() ? $currentUser->getGymAdmin() : $currentUser;

        return new JsonResponse((new WorkoutDayTransformer($this->getEm()))->transform($day));
    }

    /**
     * @param WorkoutPlan $plan
     * @param Request $request
     * @return JsonResponse
     * @Route("/client/save-workout/{plan}", name="saveWorkout")
     * @Method({"POST"})
     */
    public function saveWorkoutAction(WorkoutPlan $plan, Request $request)
    {
        $currentUser = $this->getUser();
        if (!$currentUser instanceof User) {
            throw new AccessDeniedHttpException();
        }

        $user = $currentUser->isAssistant() ? $currentUser->getGymAdmin() : $currentUser;

        $planClient = $plan->getClient();
        if ($planClient !== null && !$this->clientBelongsToUser($planClient)) {
            throw new AccessDeniedHttpException();
        }

        $em = $this->getEm();
        $repo = $em->getRepository(Workout::class);

        $array = [];
        $content = $request->getContent();

        if (!empty($content)) {
            $post = json_decode($content, true);

            if (isset($post['results']) && is_array($post['results'])) {
                $array = $post['results'];
            }

            if (empty($array)) {
                $repo->removeAllWorkoutDays($plan);
            }
        }

        $service = $this->workoutPlanService;

        try {
            $data = $service->syncPlanDays($plan, $array);

            return new JsonResponse($data);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     *
     * @param WorkoutDay $day
     * @param Request $request
     * @return JsonResponse
     * @Route("/client/save-workout-day/{day}", name="saveWorkoutDay")
     * @Method({"POST"})
     */
    public function saveWorkoutDay(WorkoutDay $day, Request $request)
    {
        $currentUser = $this->getUser();
        if (!$currentUser instanceof User) {
            throw new AccessDeniedHttpException();
        }

        $user = $currentUser->isAssistant() ? $currentUser->getGymAdmin() : $currentUser;

        $result = ['success' => false];
        $content = $request->getContent();
        if ($content) {
            $post = json_decode($content, true);
            $em = $this->getEm();
            if (isset($post['exercises'])) {
                $repo = $this->getEm()->getRepository(Workout::class);
                $workouts = $repo->saveWorkoutHelper($post['exercises'], $day);
                $result['workouts'] = $workouts;
                $result['success'] = true;
            }
            if (isset($post['comment'])) {
                $day->setWorkoutDayComment($post['comment']);
                $em->persist($day);
                $em->flush();
                $result['success'] = true;
            }
        }

        return new JsonResponse($result);
    }
}
