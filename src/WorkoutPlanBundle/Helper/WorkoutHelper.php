<?php

namespace WorkoutPlanBundle\Helper;

use AppBundle\Entity\SavedWorkout;
use AppBundle\Entity\TrackWorkout;
use Doctrine\ORM\EntityManagerInterface;
use AppBundle\Transformer\Serializer\SimpleArraySerializer;
use Illuminate\Support\Collection;
use League\Fractal\Manager;
use League\Fractal;
use AppBundle\Entity\Workout;
use AppBundle\Entity\WorkoutDay;
use AppBundle\Entity\WorkoutPlan;
use AppBundle\Entity\Client;
use ReactApiBundle\Transformer\TrackWorkoutTransformer;
use ReactApiBundle\Transformer\SavedWorkoutTransformer;
use ReactApiBundle\Transformer\WorkoutDayTransformer;
use WorkoutPlanBundle\Transformer\WorkoutPlanTransformer;
use ReactApiBundle\Transformer\WorkoutTransformer;

class WorkoutHelper
{
    private EntityManagerInterface $em;
    private Manager $serializer;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->serializer = new Manager();
        $this->serializer->setSerializer(new SimpleArraySerializer);
    }

    public function preparePlansByClient(Client $client)
    {
        return collect(
            $this
                ->em
                ->getRepository(WorkoutPlan::class)
                ->getPlanByClient($client, [
                    WorkoutPlan::STATUS_ACTIVE,
                    WorkoutPlan::STATUS_INACTIVE,
                ])
        )->keyBy(function (WorkoutPlan $plan) {
            return $plan->getId();
        });
    }

    public function prepareDaysByPlans($plans)
    {
        return collect(
            $this
                ->em
                ->getRepository(WorkoutDay::class)
                ->getByPlanIds($plans->keys()->toArray())
        )->keyBy(function (WorkoutDay $day) {
            return $day->getId();
        });
    }

    /** @return Collection<Workout> */
    public function prepareWorkoutsByDays($days): Collection
    {
        return collect(
            $this
                ->em
                ->getRepository(Workout::class)
                ->getByWorkoutDayIds($days->keys()->toArray(), false)
        )->groupBy(function (Workout $workout) {
            return $workout->getWorkoutDay()->getId();
        })->map(function (\Illuminate\Support\Collection $items) {
            return $items->sortBy(function (Workout $workout) {
                $order = $workout->getOrderBy();
                if ($parent = $workout->getParent()) {
                    $order = $parent->getOrderBy() + ($workout->getOrderBy() / 1000);
                }
                return $order;
            });
        })
            ->reduce(function (\Illuminate\Support\Collection $carry, \Illuminate\Support\Collection $items) {
                $items->each(function (Workout $workout) use ($carry) {
                    $carry->put($workout->getId(), $workout);
                });
                return $carry;
            }, collect());
    }

    public function getTrackingByWorkouts($workouts)
    {
        return collect(
            $this
                ->em
                ->getRepository(TrackWorkout::class)
                ->getByWorkoutIds($workouts->keys()->toArray())
        );
    }

    public function getSavedWorkoutsByDays($days, $fromDate = false, $toDate = false)
    {
        return collect(
            $this
                ->em
                ->getRepository(SavedWorkout::class)
                ->getByWorkoutDayIds($days->keys()->toArray(), is_string($fromDate) ? $fromDate : null, is_string($toDate) ? $toDate : null)
        );
    }

    public function getInfo()
    {
        return [
            'intro' => "From here you can see your workout days with specific exercises and exercise videos in them. Some trainers use special exercise techniques to make your workouts even more awesome. This is why we just quickly want to tell you the different types of techniques.",
            'superset' => "With a superset you have to perform two exercises in a row - only having a short pause at the end of last exercise.",
            'giantset' => "A giant set is basically the same as a super set, but in a giant set you perform 3 or more exercises in a row. In the Zenfit app we call giant sets for super sets even though there are more than two exercises in a row.",
            'dropset' => "With a drop set you perform the same exercise two (or more) times, by lowering the weight, for example it could perform a biceps curl with 30lbs dumbbells for 10 reps, then 20lbs dumbbell for 8 reps and then 15lbs dumbbell for 8 reps.",
        ];
    }

    public function serializePlans($plans)
    {
        return $this
            ->serializer
            ->createData(
                new Fractal\Resource\Collection($plans, new WorkoutPlanTransformer)
            )
            ->toArray();
    }

    public function serializeDays($days, $workouts)
    {
        return $this
            ->serializer
            ->createData(
                new Fractal\Resource\Collection($days, new WorkoutDayTransformer($workouts))
            )
            ->toArray();
    }

    public function serializeExercises($workouts)
    {
        return $this
            ->serializer
            ->createData(
                new Fractal\Resource\Collection($workouts, new WorkoutTransformer($workouts))
            )
            ->toArray();
    }

    public function serializeTracking($tracking)
    {
        return $this
            ->serializer
            ->createData(
                new Fractal\Resource\Collection($tracking, new TrackWorkoutTransformer)
            )
            ->toArray();
    }

    public function serializeSavedWorkouts($savedWorkouts)
    {
        return $this
            ->serializer
            ->createData(
                new Fractal\Resource\Collection($savedWorkouts, new SavedWorkoutTransformer)
            )
            ->toArray();
    }

}
