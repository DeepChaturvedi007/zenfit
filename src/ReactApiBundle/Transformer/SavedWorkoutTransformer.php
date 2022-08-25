<?php
namespace ReactApiBundle\Transformer;

use AppBundle\Entity\SavedWorkout;
use League\Fractal\TransformerAbstract;

class SavedWorkoutTransformer extends TransformerAbstract
{
    /**
     * @param SavedWorkout $savedWorkout
     * @return array
     */
    public function transform($savedWorkout)
    {
        return [
            'id' => $savedWorkout->getId(),
            'workout_day_id' => $savedWorkout->getWorkoutDay()->getId(),
            'workout_day_name' => $savedWorkout->getWorkoutDay()->getName(),
            'plan_id' => $savedWorkout->getWorkoutDay()->getWorkoutPlan()->getId(),
            'time' => $savedWorkout->getTime(),
            'comment' => $savedWorkout->getComment(),
            'date' => $savedWorkout->getDate()->format('Y-m-d'),
        ];
    }
}
