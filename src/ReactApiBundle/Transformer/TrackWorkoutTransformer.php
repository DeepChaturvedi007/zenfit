<?php
namespace ReactApiBundle\Transformer;

use AppBundle\Entity\TrackWorkout;
use League\Fractal\TransformerAbstract;

class TrackWorkoutTransformer extends TransformerAbstract
{
    /**
     * @param TrackWorkout $trackWorkout
     * @return array
     */
    public function transform($trackWorkout)
    {
        return [
            'id' => $trackWorkout->getId(),
            'workout_id' => $trackWorkout->getWorkout()->getId(),
            'reps' => $trackWorkout->getReps(),
            'sets' => (int) $trackWorkout->getSets(),
            'weight' => $trackWorkout->getWeight(),
            'time' => $trackWorkout->getTime(),
            'date' => $trackWorkout->getDate()->format('Y-m-d'),
        ];
    }
}
