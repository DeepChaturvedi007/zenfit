<?php

namespace ExerciseBundle\Transformer;

use AppBundle\Entity\MuscleGroup;
use League\Fractal\TransformerAbstract;

class MuscleGroupTransformer extends TransformerAbstract
{
    /**
     * @return array
     */
    public function transform(MuscleGroup $mg)
    {
        return [
            'id' => $mg->getId(),
            'name' => $mg->getName()
        ];
    }
}
