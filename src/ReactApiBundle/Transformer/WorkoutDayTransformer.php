<?php
namespace ReactApiBundle\Transformer;

use AppBundle\Entity\Workout;
use AppBundle\Entity\WorkoutDay;
use AppBundle\Entity\WorkoutPlan;
use League\Fractal\TransformerAbstract;
use Illuminate\Support\Collection;

class WorkoutDayTransformer extends TransformerAbstract
{
    /**
     * @var Collection
     */
    private $workouts;

    public function __construct(Collection $workouts = null)
    {
        $this->workouts = $workouts ? $workouts : collect();
    }

    /**
     * @param WorkoutDay $workoutDay
     * @return array
     */
    public function transform($workoutDay)
    {
        $workoutPlan = $workoutDay->getWorkoutPlan();
        $exercises = $this->workouts->filter(function(Workout $workout) use ($workoutDay) {
            return $workout->getWorkoutDay()->getId() === $workoutDay->getId();
        });

        $image = $workoutDay->getImage();
        if (!$image) {
            $workout = $workoutDay->getWorkouts()[0] ? $workoutDay->getWorkouts()[0] : null;
            $image = $workout ? $workout->getExercise()->getPictureUrl() : null;
        }

        return [
            'id' => $workoutDay->getId(),
            'plan_id' => $workoutPlan->getId(),
            'name' => $workoutDay->getName(),
            'comment' => strip_tags(br2nl($workoutDay->getWorkoutDayComment())),
            'order' => (int) $workoutDay->getOrder(),
            'image' => $image,
            'exercises' => $exercises->count(),
            'supersets' => $this->getSupersetsCounts($exercises),
            'time' => $this->getTotalTime($exercises),
        ];
    }

    private function getSupersetsCounts(Collection $exercises)
    {
        $items = $exercises->filter(function(Workout $workout) use ($exercises) {
            return $exercises->filter(function(Workout $children) use ($workout) {
                return $children->getParent() === $workout;
            })->count();
        });
        return $items->count();
    }

    private function getTotalTime(Collection $exercises)
    {
        return $exercises->reduce(function ($total, Workout $workout) {
            return $total + (int) $workout->getRest();
        }, 0);
    }
}
