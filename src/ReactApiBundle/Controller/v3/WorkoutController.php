<?php

namespace ReactApiBundle\Controller\v3;

use AppBundle\Consumer\PdfGenerationEvent;
use AppBundle\Entity\TrackWorkout;
use AppBundle\Entity\WorkoutPlan;
use AppBundle\Entity\SavedWorkout;
use AppBundle\Services\PdfService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use WorkoutPlanBundle\Helper\WorkoutHelper;
use AppBundle\Transformer\Serializer\SimpleArraySerializer;
use ReactApiBundle\Transformer\TrackWorkoutTransformer;
use ReactApiBundle\Transformer\SavedWorkoutTransformer;
use WorkoutPlanBundle\Transformer\WorkoutPlanTransformer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use ReactApiBundle\Controller\Controller as sfController;
use League\Fractal\Manager;
use League\Fractal;
use Symfony\Component\Routing\Annotation\Route;
use AppBundle\Repository\ClientRepository;
use AppBundle\Repository\TrackWorkoutRepository;
use AppBundle\Repository\WorkoutPlanRepository;
use AppBundle\Repository\WorkoutRepository;
use AppBundle\Repository\WorkoutDayRepository;

/**
 * @Route("/workout")
 */
class WorkoutController extends sfController
{
    private WorkoutHelper $workoutHelper;
    private MessageBusInterface $messageBus;
    private TrackWorkoutRepository $trackWorkoutRepository;
    private WorkoutPlanRepository $workoutPlanRepository;
    private WorkoutRepository $workoutRepository;
    private WorkoutDayRepository $workoutDayRepository;

    public function __construct(
        EntityManagerInterface $em,
        WorkoutHelper $workoutHelper,
        MessageBusInterface $messageBus,
        ClientRepository $clientRepository,
        WorkoutPlanRepository $workoutPlanRepository,
        TrackWorkoutRepository $trackWorkoutRepository,
        WorkoutRepository $workoutRepository,
        WorkoutDayRepository $workoutDayRepository
    ) {
        $this->messageBus = $messageBus;
        $this->workoutHelper = $workoutHelper;
        $this->workoutPlanRepository = $workoutPlanRepository;
        $this->trackWorkoutRepository = $trackWorkoutRepository;
        $this->workoutRepository = $workoutRepository;
        $this->workoutDayRepository = $workoutDayRepository;

        parent::__construct($em, $clientRepository);
    }

    /**
     * @Route("", methods={"GET"})
     */
    public function getAction(Request $request): JsonResponse
    {
        $client = $this->requestClientByToken($request);

        if (!$client) {
            return new JsonResponse(null, JsonResponse::HTTP_UNAUTHORIZED);
        }

        //prepare data
        $info = $this->workoutHelper->getInfo();
        $plans = $this->workoutHelper->preparePlansByClient($client);
        $days = $this->workoutHelper->prepareDaysByPlans($plans);
        $workouts = $this->workoutHelper->prepareWorkoutsByDays($days);
        $tracking = $this->workoutHelper->getTrackingByWorkouts($workouts);
        $savedWorkouts = $this->workoutHelper->getSavedWorkoutsByDays($days);

        //serialize data
        $plans = $this->workoutHelper->serializePlans($plans);
        $days = $this->workoutHelper->serializeDays($days, $workouts);
        $exercises = $this->workoutHelper->serializeExercises($workouts);
        $tracking = $this->workoutHelper->serializeTracking($tracking);
        $savedWorkouts = $this->workoutHelper->serializeSavedWorkouts($savedWorkouts);

        return new JsonResponse(compact('info', 'plans', 'days', 'exercises', 'tracking', 'savedWorkouts'));
    }

   /**
    * @Route("/stats", methods={"GET"})
    */
    public function getStatsAction(Request $request): JsonResponse
    {
        $params      = $request->query;
        $client      = $this->requestClientByToken($request);
        $limit       = $params->getInt('limit', 20);
        $offset      = $params->getInt('offset', 0);
        $fromDate    = (new \DateTime((string) $params->get('from')))->format('Y-m-d');
        $toDate      = (new \DateTime((string) $params->get('to')))->format('Y-m-d');
        $type        = $params->get('type');
        $exerciseId  = $params->getInt('exerciseId', 0);

        if (!$client) {
            return new JsonResponse(null, JsonResponse::HTTP_UNAUTHORIZED);
        }

        $workoutPlan = $this
            ->workoutPlanRepository
            ->getPlanByClient($client, [WorkoutPlan::STATUS_ACTIVE], true);

        //initial values before requests
        $exercises = [];
        $tracking = [];
        $exercise = null;
        $workouts = null;
        $plans = $this->workoutHelper->preparePlansByClient($client);
        $days = $days = $this->workoutHelper->prepareDaysByPlans($plans);
        $savedWorkouts = $this->workoutHelper->getSavedWorkoutsByDays($days, $fromDate, $toDate);

        $exercises = $workoutPlan ? $this
                ->trackWorkoutRepository
                ->getWorkoutPlanStats($limit, $offset, $workoutPlan)
                : collect([]);

        if (!$exerciseId && $exercises->count() > 0) {
            //get first exercise
            $exerciseId = $exercises->first()['id'];
        }

        if ($exerciseId) {
            $exercise = $this
                ->trackWorkoutRepository
                ->getByExercise($workoutPlan, $exerciseId);
        }

        $tracking = $this->workoutHelper->serializeTracking($tracking);
        $savedWorkouts = $this->workoutHelper->serializeSavedWorkouts($savedWorkouts);
        $currentPlan = $workoutPlan ? (new WorkoutPlanTransformer())->transform($workoutPlan) : null;

        return new JsonResponse(compact('currentPlan', 'exercises', 'exercise', 'tracking', 'savedWorkouts'));
    }

    /**
     * @Route("/tracking", methods={"POST"})
     */
    public function postTrackingAction(Request $request): JsonResponse
    {
        $input = collect($this->requestInput($request, true));

        $exists = function ($x) {
            return isset($x);
        };

        $keyById = function ($x) {
            return $x->getId();
        };

        $ids = $input->pluck('id')->filter($exists)->unique();
        $workoutIds = $input->pluck('workout_id')->filter($exists)->unique();
        $dates = $input->pluck('date')->filter($exists)->unique();

        if ($ids->count() === 0) {
            return new JsonResponse(null, JsonResponse::HTTP_NOT_MODIFIED);
        }

        try {
            $workouts = collect(
                $this
                    ->workoutRepository
                    ->getByIds($workoutIds->toArray())
            )->keyBy($keyById);

            $records = collect(
                $this
                    ->trackWorkoutRepository
                    ->getByWorkoutIdsAndDates($workoutIds->toArray(), $dates->toArray())
            )->keyBy($keyById);

            $delete = $records
                ->keys()
                ->diff($ids);

            foreach ($delete->toArray() as $id) {
                if ($records->has($id)) {
                    $records->get($id)->setDeleted(true);
                    $records->forget($id);
                }
            }

            $entities = [];

            foreach ($input->toArray() as $item) {
                $item = collect($item);
                $id = $item->get('id');
                $workout = $workouts->get($item->get('workout_id'));
                $entity = $records->get($id);

                $date = new \DateTime($item->get('date'));
                if ($id) {
                    if (!$entity) continue;
                } else {
                    if (!$workout) continue;

                    $entity = (new TrackWorkout($workout, $date));
                    $this->em->persist($entity);
                }

                $entity
                    ->setReps($item->get('reps'))
                    ->setWeight($item->get('weight'))
                    ->setSets($item->get('sets'))
                    ->setTime($item->get('time'))
                    ->setDate($date);

                $entities[] = $entity;
            }

            $this->em->flush();

            $fractal = new Manager();
            $serializer = $fractal->setSerializer(new SimpleArraySerializer);
            $data = $serializer
                ->createData(
                    new Fractal\Resource\Collection($entities, new TrackWorkoutTransformer)
                )
                ->toArray();

            return new JsonResponse($data);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => $e->getMessage()
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @Route("/save-workout", methods={"POST"})
     */
    public function saveWorkoutAction(Request $request): JsonResponse
    {
        try {
            $input = $this->requestInput($request);
            $day = $this
                ->workoutDayRepository
                ->find($input->workoutDayId);

            if ($day === null) {
                return new JsonResponse(null, JsonResponse::HTTP_NOT_MODIFIED);
            }

            $savedWorkout = new SavedWorkout();
            $savedWorkout
                ->setWorkoutDay($day)
                ->setComment($input->comment)
                ->setTime($input->time)
                ->setDate(new \DateTime('now'));

            $this->em->persist($savedWorkout);
            $this->em->flush();

            $transformer = new SavedWorkoutTransformer();
            return new JsonResponse($transformer->transform($savedWorkout));
        } catch (\Throwable $e) {
            return new JsonResponse([
                'message' => 'Something went wrong!'
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @Route("/save-pdf", methods={"POST"})
     */
    public function saveWorkoutPDFAction(Request $request): JsonResponse
    {
        $client = $this->requestClientByToken($request);
        $input = $this->requestInput($request);

        if (!$client) {
            return new JsonResponse(null, JsonResponse::HTTP_UNAUTHORIZED);
        }

        $email = trim($client->getEmail());
        if ($email === '') {
            throw new BadRequestHttpException('Client does not have email address');
        }
        $name = trim($client->getName());

        $this
            ->messageBus->dispatch(
                new PdfGenerationEvent(
                    PdfService::TYPE_WORKOUT,
                    (int) $input->plan,
                    $name,
                    $email,
                    PdfService::V1
                )
            );

        return new JsonResponse(['success' => true]);
    }
}
