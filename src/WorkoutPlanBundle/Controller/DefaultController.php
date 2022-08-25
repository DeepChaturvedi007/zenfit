<?php

namespace WorkoutPlanBundle\Controller;

use AppBundle\Controller\Controller;
use AppBundle\Entity\Client;
use AppBundle\Entity\Equipment;
use AppBundle\Entity\MuscleGroup;
use AppBundle\Entity\User;
use AppBundle\Entity\WorkoutDay;
use AppBundle\Entity\WorkoutPlan;
use AppBundle\Repository\MuscleGroupRepository;
use AppBundle\Services\WorkoutPlanService;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Mobile_Detect;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @Route("/workout")
 */
class DefaultController extends Controller
{
    private WorkoutPlanService $workoutPlanService;

    public function __construct(
        EntityManagerInterface $em,
        WorkoutPlanService $workoutPlanService,
        TokenStorageInterface $tokenStorage
    ) {
        $this->workoutPlanService = $workoutPlanService;

        parent::__construct($em, $tokenStorage);
    }

    /**
     * @Route("/clients/{client}", name="workout_client")
     * @Route("/templates", name="workout_templates")
     * @param Client $client
     * @return Response
     */
    public function overviewAction(Client $client = null)
    {
        $em = $this->getEm();
        $currentUser = $this->getUser();
        if (!$currentUser instanceof User) {
            throw new AccessDeniedHttpException();
        }

        $user = $currentUser->isAssistant() ? $currentUser->getGymAdmin() : $currentUser;

        $repo = $em->getRepository(WorkoutPlan::class);
        $templates = $repo->getAllByUser($user, true);

        $plans = $templates;
  	    $parameters = array(
  		    'plans' => $plans,
  		    'client' => $client,
  		    'templates' => $templates
  	    );

	      $parameters['demoClient'] = null;

  	    if($client) {
            $parameters['demoClient'] = $client->getDemoClient();
  		      $plans = $repo->getAllByClientAndUser($client, $user);
  		      $parameters['plans'] = $plans;

            abort_unless(is_owner($user, $client), 403, 'This client does not belong to you.');
  	    }

        return $this->render('@WorkoutPlan/overview.html.twig', $parameters);
    }

    /**
     * @Route("/clients/{client}/plan/{plan}", name="workout_client_edit")
     * @Route("/templates/{plan}", name="workout_templates_edit")
     * @param WorkoutPlan $plan
     * @param Client $client
     * @return Response
     */
    public function editAction(WorkoutPlan $plan, $client = null)
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
        if($client) {
            $clientRepo = $em->getRepository(Client::class);
            $client = $clientRepo->find($client);
            if ($client === null) {
                throw new NotFoundHttpException();
            }

            if (!$this->clientBelongsToUser($client)) {
                throw new AccessDeniedHttpException();
            }

  			    $parameters['demoClient'] = $client->getDemoClient();
        }

        $mobileDetector = new Mobile_Detect;

        $view = ($mobileDetector->isMobile() && !$mobileDetector->isTablet()) ?
            '@WorkoutPlan/editor_mobile.html.twig' :
            '@WorkoutPlan/editor.html.twig';

        $templates = $em
            ->getRepository(WorkoutPlan::class)
            ->getAllByUser($user, true);

        $planSettings = $plan->getSettings();

        /** @var MuscleGroupRepository $muscleGroupsRepo */
        $muscleGroupsRepo = $em
            ->getRepository(MuscleGroup::class);

        $muscleGroups = $muscleGroupsRepo->getAllMuscleGroups();

        $equipments = $em
            ->getRepository(Equipment::class)
            ->getAllEquipment();

  	    $parameters = array(
    		    'showMessage' => false,
    		    'plan' => $plan,
    		    'client' => $client,
    		    'planSettings' => $planSettings,
    		    'templates' => $templates,
            'muscleGroups' => $muscleGroups,
            'equipments' => $equipments,
    		    'chosenTemplate' => null,
    		    'lastUpdated' => $plan->getLastUpdated(),
  		      'demoClient' => null,
            'unreadClientMessagesCount' => 0
  	    );

        return $this->render($view, $parameters);
    }

    /**
     * @Route("/day/{day}", name="workoutDayEditorMobile")
     * @Method({"GET"})
     */
    public function workoutDayEditorMobileAction(WorkoutDay $day): Response
    {
        $currentUser = $this->getUser();
        if (!$currentUser instanceof User) {
            throw new AccessDeniedHttpException();
        }

        $user = $currentUser->isAssistant() ? $currentUser->getGymAdmin() : $currentUser;

        $workoutPlanUser = $day->getWorkoutPlan()->getUser();
        if ($workoutPlanUser === null) {
            throw new \RuntimeException('Workout plan has no user attached');
        }

        if ($workoutPlanUser->getId() !== $user->getId()) {
            throw new AccessDeniedHttpException();
        }

        $unreadClientMessagesCount = 0;
        $client = $day->getWorkoutPlan()->getClient();
        if ($client !== null) {
            if (!$this->clientBelongsToUser($client)) {
                throw new AccessDeniedHttpException();
            }

            $unreadClientMessagesCount = $user->unreadMessagesCount($client);
        }

        $em = $this->getEm();
	    $muscleGroups = $em->createQuery("SELECT mg FROM AppBundle:MuscleGroup mg")->getArrayResult();
	    $exerciseTypes = $em->createQuery("SELECT e FROM AppBundle:ExerciseType e")->getArrayResult();
	    $equipments = $em->createQuery("SELECT e FROM AppBundle:Equipment e")->getArrayResult();
	    $workoutTypes = $em->createQuery("SELECT wt FROM AppBundle:WorkoutType wt")->getArrayResult();
	    $workoutId = $day->getWorkoutPlan()->getId();
	    $demoClient = $client
	        ? $client->getDemoClient()
	        : null;
	    $totalActiveClients = $user->getTotalActiveClients();

        return $this->render('@WorkoutPlan/editor_day_mobile.html.twig', compact(
            	'client','day', 'muscleGroups', 'exerciseTypes', 'equipments', 'workoutTypes',
	            'demoClient', 'workoutId', 'totalActiveClients', 'unreadClientMessagesCount'
        ));
    }

    /**
     * @Route("/from-scratch/{plan}", name="workout_from_scratch")
     * @param WorkoutPlan $plan
     * @return RedirectResponse
     */
    public function fromScratchAction(WorkoutPlan $plan)
    {
        $currentUser = $this->getUser();
        if (!$currentUser instanceof User) {
            throw new AccessDeniedHttpException();
        }

        $user = $currentUser->isAssistant() ? $currentUser->getGymAdmin() : $currentUser;

        $planUser = $plan->getUser();
        if ($planUser === null) {
            throw new \RuntimeException('Plan has no user attached');
        }
        if ($planUser->getId() !== $user->getId()) {
            throw new AccessDeniedHttpException();
        }
        $planClient = $plan->getClient();
        if ($planClient !== null && !$this->clientBelongsToUser($planClient)) {
            throw new AccessDeniedHttpException();
        }

        $service = $this->workoutPlanService;
        $service->addDay($plan, 'Day 1');

        return $this->workoutEditorRedirect($plan);
    }

}
