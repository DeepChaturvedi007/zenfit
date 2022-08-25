<?php

namespace ReactApiBundle\Controller\v2;

use AppBundle\Consumer\PdfGenerationEvent;
use AppBundle\Entity\TrackWorkout;
use AppBundle\Entity\Workout;
use AppBundle\Entity\WorkoutDay;
use AppBundle\Entity\WorkoutPlan;
use AppBundle\Entity\SavedWorkout;
use AppBundle\Services\PdfService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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

/**
 * @Route("/v2/workout")
 */
class WorkoutController extends sfController
{
    private WorkoutHelper $workoutHelper;
    private MessageBusInterface $messageBus;

    public function __construct(
        EntityManagerInterface $em,
        WorkoutHelper $workoutHelper,
        MessageBusInterface $messageBus,
        ClientRepository $clientRepository
    ) {
        $this->messageBus = $messageBus;
        $this->workoutHelper = $workoutHelper;

        parent::__construct($em, $clientRepository);
    }

    /**
     * @Route("", methods={"GET"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getAction(Request $request)
    {
        $client = $this->requestClient($request);

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

        //check if client has been activated
        //and created after 5th of Jan
        $date = new \DateTime('2021-01-05');
        if (!$client->hasBeenActivated() && $client->getCreatedAt() > $date) {
            $plans = [];
        }

        return new JsonResponse(compact('info', 'plans', 'days', 'exercises', 'tracking', 'savedWorkouts'));
    }

   /**
    * @Route("/stats", methods={"GET"})
    *
    * @param Request $request
    * @return JsonResponse
    */
    public function getStatsAction(Request $request)
    {
        $params      = $request->query;
        $client      = $this->requestClient($request);
        $limit       = $params->getInt('limit', 20);
        $offset      = $params->getInt('offset', 0);
        $fromDate    = (new \DateTime($params->get('from')))->format('Y-m-d');
        $toDate      = (new \DateTime($params->get('to')))->format('Y-m-d');
        $type        = $params->get('type');
        $exerciseId  = $params->getInt('exerciseId', 0);

        if (!$client) {
            return new JsonResponse(null, JsonResponse::HTTP_UNAUTHORIZED);
        }

        $em = $this->em;

        $workoutPlan = $em
            ->getRepository(WorkoutPlan::class)
            ->getPlanByClient($client, [WorkoutPlan::STATUS_ACTIVE], true);

        //initial values before requests
        $exercises = [];
        $tracking = [];
        $exercise = null;
        $workouts = null;
        $plans = $this->workoutHelper->preparePlansByClient($client);
        $days = $days = $this->workoutHelper->prepareDaysByPlans($plans);
        $savedWorkouts = $this->workoutHelper->getSavedWorkoutsByDays($days, $fromDate, $toDate);

        $exercises = $workoutPlan ? $em
                ->getRepository(TrackWorkout::class)
                ->getWorkoutPlanStats($limit, $offset, $workoutPlan)
                : collect([]);

        if (!$exerciseId && $exercises->count() > 0) {
            //get first exercise
            $exerciseId = $exercises->first()['id'];
        }

        if ($exerciseId) {
            $exercise = $em
                ->getRepository(TrackWorkout::class)
                ->getByExercise($workoutPlan, $exerciseId);
        }

        $tracking = $this->workoutHelper->serializeTracking($tracking);
        $savedWorkouts = $this->workoutHelper->serializeSavedWorkouts($savedWorkouts);
        $currentPlan = $workoutPlan ? (new WorkoutPlanTransformer())->transform($workoutPlan) : null;

        return new JsonResponse(compact('currentPlan', 'exercises', 'exercise', 'tracking', 'savedWorkouts'));
    }

    /**
     * @Route("/tracking", methods={"POST"})
     *
     * @param Request $request
     * @return Response
     */
    public function postTrackingAction(Request $request)
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

        $em = $this->em;

        try {
            $workouts = collect(
                $em
                    ->getRepository(Workout::class)
                    ->getByIds($workoutIds->toArray())
            )->keyBy($keyById);

            $records = collect(
                $em
                    ->getRepository(TrackWorkout::class)
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
                    $em->persist($entity);
                }

                $entity
                    ->setReps($item->get('reps'))
                    ->setWeight($item->get('weight'))
                    ->setSets($item->get('sets'))
                    ->setTime($item->get('time'))
                    ->setDate($date);

                $entities[] = $entity;
            }

            $em->flush();

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
     *
     * @param Request $request
     * @return Response
     */
    public function saveWorkoutAction(Request $request)
    {
        try {
            $em = $this->em;
            $input = $this->requestInput($request);
            $day = $em->getRepository(WorkoutDay::class)->find($input->workoutDayId);
            if ($day === null) {
                throw new NotFoundHttpException('Workout day not found');
            }
            $savedWorkout = new SavedWorkout();
            $savedWorkout
                ->setWorkoutDay($day)
                ->setComment($input->comment)
                ->setTime($input->time)
                ->setDate(new \DateTime('now'));

            $em->persist($savedWorkout);
            $em->flush();

            $transformer = new SavedWorkoutTransformer();
            return new JsonResponse($transformer->transform($savedWorkout));
        } catch (\Throwable $e) {
            return new JsonResponse([
                'message' => 'Something went wrong!'
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Something went wrong!'
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @Route("/save-pdf", methods={"POST"})
     *
     * @param Request $request
     * @return Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function saveWorkoutPDFAction(Request $request)
    {
        $client = $this->requestClient($request);
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
