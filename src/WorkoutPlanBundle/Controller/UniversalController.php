<?php

namespace WorkoutPlanBundle\Controller;

use AppBundle\Entity\Client;
use AppBundle\Entity\User;
use AppBundle\Entity\WorkoutDay;
use AppBundle\Services\WorkoutPlanService;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use AppBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use AppBundle\Entity\WorkoutPlan;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use WorkoutPlanBundle\Transformer\WorkoutPlanTransformer;

/**
 * @Route("/workout")
 */
class UniversalController extends Controller
{
    private WorkoutPlanService $workoutPlanService;

    public function __construct(
        WorkoutPlanService $workoutPlanService,
        EntityManagerInterface $em,
        TokenStorageInterface $tokenStorage
    ) {
        $this->workoutPlanService = $workoutPlanService;

        parent::__construct($em, $tokenStorage);
    }

    /**
     * @Route("", name="workout_create")
     * @Method({"POST"})
     * @param Request $request
     * @return JsonResponse|RedirectResponse
     */
    public function createAction(Request $request)
    {
        $em = $this->getEm();
        $currentUser = $this->getUser();
        if (!$currentUser instanceof User) {
            throw new AccessDeniedHttpException();
        }

        $user = $currentUser->isAssistant() ? $currentUser->getGymAdmin() : $currentUser;

        $name = (string) $request->request->get('name');
        $explaination = $request->request->get('explaination', null);
        $workoutsPerWeek = $request->request->get('workoutsPerWeek', null);
        $duration = $request->request->get('duration', null);
        $level = $request->request->get('level', null);
        $location = $request->request->get('location', null);
        $gender = $request->request->get('gender', null);
        $comment = null;

        if(!$name) {
            $now = new \DateTime('now');
            $now = $now->format('Y-m-d');
            $name = "Workout {$now}";
        }

        if ($client = $request->request->get('client')) {
            /** @var ?Client $client */
            $client = $em
                ->getRepository(Client::class)
                ->find($client);
            if ($client === null) {
                throw new NotFoundHttpException();
            }

            if (!$this->clientBelongsToUser($client)) {
                throw new AccessDeniedHttpException();
            }

        } else {
            $client = null;
        }

        $repo = $em->getRepository(WorkoutPlan::class);

        if ($plan = $request->request->get('plan')) {
            $plan = $repo->getByIdAndUser($plan, $user);
        } else {
            $plan = null;
        }

        if (is_array($templates = $request->request->get('templates'))) {
            $templates = $repo->getByIdsAndUser($templates, $user, true);
        } else {
            $templates = [];
        }

        if (!empty($templates)) {
            if (count($templates) == 1) {
                $name = $templates[0]->getName();
                $comment = $templates[0]->getComment();
            } else {
                $now = (new \DateTime('now'))->format('Y-m-d');
                $name = "Workout {$now}";
            }
        }

        $newPlan = $this
            ->workoutPlanService
            ->setName($name)
            ->setExplaination($explaination)
            ->setComment($comment)
            ->setWorkoutsPerWeek($workoutsPerWeek)
            ->setDuration($duration)
            ->setLevel($level)
            ->setGender($gender)
            ->setLocation($location)
            ->createPlan($user, $client, $plan, $templates);

        if ($request->isXmlHttpRequest()) {
            $data = [];

            if ($plan) {
                $data['id'] = $newPlan->getId();
                $data['name'] = $newPlan->getName();
                $data['updated_at'] = $newPlan->getLastUpdated();
            }

            return new JsonResponse($data, 200);
        }

        return $this->workoutEditorRedirect($newPlan);
    }

    /**
     * @Method({"GET","DELETE","POST"})
     * @Route("/{plan}/delete", name="workout_delete")
     * @param WorkoutPlan $plan
     * @param Request $request
     * @return RedirectResponse|JsonResponse
     */
    public function deleteAction(WorkoutPlan $plan, Request $request)
    {
        $currentUser = $this->getUser();
        if (!$currentUser instanceof User) {
            throw new AccessDeniedHttpException();
        }

        $user = $currentUser->isAssistant() ? $currentUser->getGymAdmin() : $currentUser;

        try {
            $planClient = $plan->getClient();

            if ($planClient !== null && !$this->clientBelongsToUser($planClient)) {
                throw new AccessDeniedHttpException();
            }

            $plan->setDeleted(true);
            $this->getEm()->flush();

            if ($request->isXmlHttpRequest()) {
                return new JsonResponse([
                    'id' => $plan->getId(),
                ], 200);
            }
        } catch (\Exception $error) {
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse([
                    'message' => $error->getMessage(),
                ], 422);
            }
        }

        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * @Route("/deleteWorkoutDay/{day}", name="deleteWorkoutDay")
     * @param WorkoutDay $day
     * @return RedirectResponse
     */
    public function deleteWorkoutDayAction(WorkoutDay $day)
    {
        $currentUser = $this->getUser();
        if (!$currentUser instanceof User) {
            throw new AccessDeniedHttpException();
        }

        $user = $currentUser->isAssistant() ? $currentUser->getGymAdmin() : $currentUser;

        $plan = $day->getWorkoutPlan();
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

        $em = $this->getEm();
        $em->remove($day);
        $em->flush();

        return $this->workoutEditorRedirect($plan);
    }

    /**
     * @Route("/{plan}/update", name="workout_update", methods={"POST"})
     * @return JsonResponse|RedirectResponse
     */
    public function updateAction(WorkoutPlan $plan, Request $request)
    {
        $currentUser = $this->getUser();
        if (!$currentUser instanceof User) {
            throw new AccessDeniedHttpException();
        }

        $user = $currentUser->isAssistant() ? $currentUser->getGymAdmin() : $currentUser;

        $planClient = $plan->getClient();
        $planUser = $plan->getUser();
        if ($planUser === null) {
            throw new \RuntimeException('Plan has no user attached');
        }

        if ($planUser->getId() !== $user->getId() || ($planClient !== null && !$this->clientBelongsToUser($planClient))) {
            throw new AccessDeniedHttpException();
        }

        try {
            $this
                ->workoutPlanService
                ->setPlan($plan)
                ->setWorkoutPlanMeta()
                ->setName($request->request->get('name'))
                ->setExplaination($request->request->get('explaination'))
                ->setComment($request->request->get('comment'))
                ->setWorkoutsPerWeek($request->request->get('workoutsPerWeek'))
                ->setGender($request->request->get('gender'))
                ->setLocation($request->request->get('location'))
                ->setDuration($request->request->get('duration'))
                ->setLevel($request->request->get('level'))
                ->setStatus($request->request->get('status'))
                ->setTemplates((array) ($request->request->get('templates') ?? []))
                ->setSettings($request->request->get('settings'))
                ->updatePlan($user, $plan);

                if ($request->isXmlHttpRequest()) {
                    return new JsonResponse((new WorkoutPlanTransformer)->transform($plan));
                }
        } catch (\Exception $error) {
            return new JsonResponse([
                'message' => $error->getMessage(),
            ], 422);
        }

        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * @param WorkoutPlan $plan
     * @param Request $request
     * @return RedirectResponse|JsonResponse
     * @Route("/save-as-template/{plan}", name="workout_create_template")
     * @Method({"POST"})
     */
    public function saveAsTemplateAction(WorkoutPlan $plan, Request $request)
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

        $name = $request->request->get('name');
        $comment = $request->request->get('comment');

        try {
            $template = $this
                ->workoutPlanService
                ->setName($name)
                ->setComment($plan->getComment())
                ->setExplaination($comment)
                ->createPlan($user, null, $plan);

            if ($request->isXmlHttpRequest()) {
                return new JsonResponse([
                    'id' => $template->getId(),
                    'message' => 'Template "' . $name . '" successfully saved.',
                ], 201);
            }

            return $this->redirectToRoute('workout_templates');
        } catch (\Exception $e) {
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse([
                    'message' => 'Cannot save template "' . $name . '", please try again.',
                ], 422);
            }
        }

        return $this->workoutEditorRedirect($plan);
    }


    /**
     * @Route("/day/{plan}", name="workout_day")
     * @Method({"POST","PUT","PATCH"})
     * @param WorkoutPlan $plan
     * @param Request $request
     * @return RedirectResponse|JsonResponse
     */
    public function dayAction(WorkoutPlan $plan, Request $request)
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

        $em = $this->getEm();
        $repo = $em->getRepository(WorkoutDay::class);

        $name = (string) $request->request->get('name');
        $id = $request->request->get('id');

        if (empty($name)) {
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse([
                    'message' => 'The name field is required.',
                ], 422);
            }

            return $this->workoutEditorRedirect($plan);
        }

        $day = $id ? $repo->find($id) : null;

        try {
            $last = false;

            if ($day) {
                $day->setName($name);
                $em->flush();
            } else {
                $service = $this->workoutPlanService;
                $order = $repo->getLastOrderByPlan($plan);

                $day = $service->addDay($plan, $name, $order + 1);
                $last = true;
            }

            if ($request->isXmlHttpRequest()) {
                return new JsonResponse([
                    'id' => $day->getId(),
                    'name' => $day->getName(),
                    'order' => $day->getOrder(),
                    'last' => $last,
                ], 200);
            }
        } catch (\Exception $e) {
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse([
                    'message' => $e->getMessage(),
                ], 422);
            }
        }

        return $this->redirectToRoute('workoutDayEditorMobile', ['day' => $day->getId()]);
    }

    /**
     * @Route("/day/{plan}/clone", name="workout_day_clone")
     * @Method({"POST"})
     */
    public function cloneDayAction(WorkoutPlan $plan, Request $request): Response
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

        $em = $this->getEm();
        $id = $request->request->get('id');
        $name = $request->request->get('name');

        if (empty($name)) {
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse([
                    'message' => 'The name field is required.',
                ], 422);
            }

            return $this->workoutEditorRedirect($plan);
        }

        /**
         * @var $day WorkoutDay
         */
        $day = $em
            ->getRepository(WorkoutDay::class)
            ->getByIdAndPlan((int) $id, $plan);

        if (!$day) {
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse([
                    'message' => 'Day not found',
                ], 404);
            }

            return $this->workoutEditorRedirect($plan);
        }

        $service = $this->workoutPlanService;

        try {
            $newDay = $service->cloneDay($name, $day, $plan, 0, true);

            if ($request->isXmlHttpRequest()) {
                return new JsonResponse([
                    'id' => $newDay->getId(),
                    'name' => $newDay->getName(),
                    'order' => $newDay->getOrder(),
                ], 200);
            }
        } catch (\Exception $e) {
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse([
                    'message' => $e->getMessage(),
                ], 422);
            }
        }

        return $this->workoutEditorRedirect($plan);
    }

}
