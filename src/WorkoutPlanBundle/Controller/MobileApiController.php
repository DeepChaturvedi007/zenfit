<?php

namespace WorkoutPlanBundle\Controller;

use AppBundle\Entity\Exercise;
use AppBundle\Repository\TrackWorkoutRepository;
use AppBundle\Repository\WorkoutRepository;
use AppBundle\Services\WorkoutPlanService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use WorkoutPlanBundle\Transformer\WorkoutTransformer;
use AppBundle\Entity\WorkoutPlan;
use AppBundle\Entity\WorkoutDay;
use AppBundle\Entity\Workout;
use AppBundle\Entity\Client;
use AppBundle\Entity\TrackWorkout;

/**
 * @Route("/api/workout/mobile")
 */
class MobileApiController extends Controller
{
    /**
    * @Route("/getWeightLiftedHistory")
    */
    public function getWeightLiftedHistoryAction(Request $request): JsonResponse
    {
      $workoutId = $request->query->get('workout');
      $workout = $this
          ->getEm()
          ->getRepository(Workout::class)
          ->find($workoutId);

      $data = [];
      $sessions = $this
          ->getEm()
          ->getRepository(TrackWorkout::class)
          ->createQueryBuilder('wt')
          ->where('wt.workout = :workout')
          ->orderBy('wt.date', 'DESC')
          ->setParameter('workout', $workout)
          ->getQuery()
          ->getResult();

      foreach($sessions as $session) {
          /** @var TrackWorkout $session */
          $data[] = array(
              'id' => $session->getId(),
              'reps' => $session->getReps(),
              'sets' => $session->getSets(),
              'time' => $session->getTime(),
              'weight' => $session->getWeight(),
              'workout' => $workoutId,
              'date' => $session->getDate()->format('Y-m-d')
          );
      }

      return new JsonResponse($data);
    }

    /**
     * @Route("/createWorkouts/{day}/{parentWorkout}", defaults={"parentWorkout" = null})
     */
    public function createWorkoutsAction(Request $request, WorkoutDay $day, ?int $parentWorkout = null): Response
    {
        $content = $request->getContent();
        $post = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        try {
            $data = $this->createWorkouts($day, $parentWorkout, $post);

            return new JsonResponse([
                'success' => true,
                'oldWorkouts' => $data['oldWorkouts'],
                'newWorkouts' => $data['newWorkouts'],
            ], 200);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'msg' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @Route("/insertWeightLifted")
     */
    public function insertWeightLiftedAction(Request $request): JsonResponse
    {
        $content = $request->getContent();
        $post = collect(json_decode($content, true));

        $exists = function($x) { return isset($x); };
        $keyById = function($x) { return $x->getId(); };

        $ids = $post->pluck('id')->filter($exists)->unique();
        $workoutIds = $post->pluck('workout')->filter($exists)->unique();
        $dates = $post->pluck('date')->filter($exists)->unique();

        $em = $this->getEm();

        try {
            /** @var WorkoutRepository $workoutRepository */
            $workoutRepository = $em->getRepository(Workout::class);
            $workouts = $workoutRepository->getByIds($workoutIds->toArray());

            $workouts = collect($workouts)->keyBy($keyById);

            /** @var TrackWorkoutRepository $trackWorkoutRepository */
            $trackWorkoutRepository = $em->getRepository(TrackWorkout::class);
            $records = $trackWorkoutRepository
                ->getByWorkoutIdsAndDates($workoutIds->toArray(), $dates->toArray());

            $records = collect($records)->keyBy($keyById);

            $delete = $records->keys()->diff($ids);

            foreach ($delete->toArray() as $id) {
                if ($records->has($id)) {
                    $em->remove($records->get($id));
                    $records->forget($id);
                }
            }

            foreach ($post->toArray() as $item) {
                $item = collect($item);
                $id = $item->get('id');
                $workout = $workouts->get($item->get('workout'));
                $entity = $records->get($id);
                $date = new \DateTime($item->get('date'));
                if ($id) {
                    if (!$entity) continue;
                } else {
                    if (!$workout) continue;

                    $entity = new TrackWorkout($workout, $date);
                    $em->persist($entity);
                }

                $entity
                    ->setReps($item->get('reps'))
                    ->setWeight($item->get('weight'))
                    ->setSets($item->get('sets'))
                    ->setTime($item->get('time'))
                    ->setDate($date);
            }

            $em->flush();

            return new JsonResponse([
                'success' => true,
            ], 200);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'msg' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @Route("/createWeightLifted/{workout}")
     */
    public function createWeightLiftedAction(Workout $workout, Request $request): JsonResponse
    {
        $em = $this->getEm();
        $content = $request->getContent();
        $post = collect(json_decode($content, true));

        try {
            $entity = new TrackWorkout($workout, new \DateTime($post->get('date')));
            $entity
                ->setReps($post->get('reps'))
                ->setSets($post->get('sets'))
                ->setWeight($post->get('weight'))
                ->setTime($post->get('time'));

            $em->persist($entity);
            $em->flush();

            return new JsonResponse([
                'success' => true,
                'newEntity' => [
                    'id' => $entity->getId(),
                    'workout' => $workout->getId(),
                    'reps' => $entity->getReps(),
                    'sets' => $entity->getSets(),
                    'weight' => $entity->getWeight(),
                    'date' => $entity->getDate()->format('Y-m-d'),
                    'time' => $entity->getTime()
                ]
            ], 200);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'msg' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @param array<array> $workoutsData
     * @return array<mixed>
     */
    private function createWorkouts(WorkoutDay $day, ?int $parentWorkout, array $workoutsData): array
    {
        $em = $this->getEm();

  	    $workoutTransformer = new WorkoutTransformer();
  	    $oldWorkouts        = $day->getWorkouts();
  	    $oldUpdatedWorkouts = [];
  	    $parent = $parentWorkout ? $em->getRepository(Workout::class)->find( $parentWorkout ) : null;
  	    foreach ( $oldWorkouts as $workout ) {
  	    	if (!$workout->getParent()) {
  			    $oldUpdatedWorkouts[] = [
  				    'workout' => $workout
  			    ];
  		    }
  		    if ( $parent ) {
  			    if ( $workout === $parent || $workout->getParent() === $parent ) {
  				    $workout
  					    ->setSets( $parent->getSets() )
  					    ->setRest( '0' );
  				    $em->persist( $workout );
  			    }
  		    }
  	    }

  	    $oldUpdatedWorkoutsData = [];
  	    foreach ( $oldUpdatedWorkouts as $item ) {
  		    $workout = $item['workout'];
  		    $oldUpdatedWorkoutsData[] = $workoutTransformer->transform($workout);
  	    }

          $workouts = [];
          foreach ($workoutsData as $workoutData) {
              $workout = new Workout($day);
              $_exercise = $em->getReference(Exercise::class, $workoutData['exercise']['id']);
              if ($_exercise === null) {
                  throw new \RuntimeException('No Exercise');
              }
              $workouts[] = [
                  'workout' => $workout,
                  'exercise' => $workoutData['exercise']
              ];

              $workout->setExercise($_exercise);
              $workout->setInfo($workoutData['info']);
              $workout->setComment($workoutData['comment']);
              $workout->setOrderBy($workoutData['order']);
              $workout->setParent($parent);
              $workout->setReps($workoutData['reps']);
              $workout->setRest($workoutData['rest']);
              $workout->setSets($parent ? $parent->getSets() : $workoutData['sets']);
              $workout->setTime($workoutData['time']);
              $workout->setRm($workoutData['rm']);
              $workout->setTempo($workoutData['tempo']);
              $workout->setStartWeight($workoutData['startWeight']);

              $em->persist($workout);
          }

          $em->flush();

          $newWorkoutsData = [];
          foreach ($workouts as $item) {
              $workout = $item['workout'];
              $newWorkoutsData[] = [
                  'id' => $workout->getId(),
                  'info' => $workout->getInfo(),
                  'comment' => $workout->getComment(),
                  'order' => $workout->getOrderBy(),
                  'reps' => $workout->getReps(),
                  'rest' => $workout->getRest(),
                  'sets' => $workout->getSets(),
                  'time' => $workout->getTime(),
                  'rm' => $workout->getRm(),
                  'tempo' => $workout->getTempo(),
                  'startWeight' => $workout->getStartWeight(),
                  'exercise' => $item['exercise'],
                  'supers' => $parentWorkout ? null : []
              ];
          }
  	      $responseData = [ 'oldWorkouts' => $oldUpdatedWorkoutsData, 'newWorkouts' => $newWorkoutsData ];

          return $responseData;
    }
}
