<?php
namespace AppBundle\Transformer;

use AppBundle\Entity\Workout;
use League\Fractal\TransformerAbstract;

class WorkoutExerciseTransformer extends TransformerAbstract
{
    protected $availableIncludes = [
        'supers',
    ];

    protected $defaultIncludes = [
        'supers',
    ];

    /**
     * @param Workout $workout
     * @return array
     */
    public function transform($workout)
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
            'startWeight' => $workout->getStartWeight(),
            'tempo' => $workout->getTempo(),
            'rm' => $workout->getRm(),
            'type' => [
                'id' => $workoutType->getId(),
                'name' => $workoutType->getName()
            ],
            'exercise' => [
                'id' => $exercise->getId(),
                'name' => $exercise->getName(),
                'picture' => $exercise->getPictureUrl(),
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

        return $data;
    }

    /**
     * @param Workout $workout
     * @return \League\Fractal\Resource\Collection
     */
    public function includeSupers($workout)
    {
        return $this->collection($workout->getSupers(), new WorkoutExerciseTransformer);
    }
}
