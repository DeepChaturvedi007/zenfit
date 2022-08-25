<?php

namespace WorkoutPlanBundle\Transformer;

use AppBundle\Entity\Workout;
use League\Fractal\TransformerAbstract;
use Symfony\Component\DependencyInjection\ContainerInterface as Container;

class WorkoutTransformer extends TransformerAbstract
{
    /**
     * @param Workout $day
     * @return array
     */
    public function transform(Workout $workout, $isSuperSet = false)
    {
        $exercise = $workout->getExercise();
        $workoutType = $exercise->getWorkoutType();
        $data = [
            'id' => $workout->getId(),
            'comment' => $workout->getComment(),
            'order' => $workout->getOrderBy(),
            'time' => $workout->getTime(),
            'reps' => $workout->getReps(),
            'rest' => $workout->getRest(),
            'sets' => $workout->getSets(),
            'tempo' => $workout->getTempo(),
            'rm' => $workout->getRm(),
            'startWeight' => $workout->getStartWeight(),
            'type' => [
                'id' => $workoutType->getId(),
                'name' => $workoutType->getName()
            ],
            'exercise' => [
                'id' => $exercise->getId(),
                'name' => $exercise->getName(),
                'picture' => $exercise->getPictureUrl() ? $exercise->getPictureUrl() : '/web/images/exercise_thumbnails',
                'video' => $exercise->getVideoUrl(),
            ],
        ];

        if ($muscleGroup = $exercise->getMuscleGroup()) {
            $data['exercise']['muscle'] = [
                'id' => $muscleGroup->getId(),
                'name' => $muscleGroup->getName()
            ];
        } else {
            $data['exercise']['muscle'] = null;
        }

        if ($exerciseType = $exercise->getExerciseType()) {
            $data['exercise']['type'] = [
                'id' => $exerciseType->getId(),
                'name' => $exerciseType->getName()
            ];
        } else {
            $data['exercise']['type'] = null;
        }

        if ($equipment = $exercise->getEquipment()) {
            $data['exercise']['equipment'] = [
                'id' => $equipment->getId(),
                'name' => $equipment->getName()
            ];
        } else {
            $data['exercise']['equipment'] = null;
        }

        if (!$isSuperSet) {
            $data['supers'] = array_map(function(Workout $item) {
                return $this->transform($item, true);
            }, $workout->getSupers()->toArray());
        }

        return $data;
    }
}
