<?php

namespace ExerciseBundle\Transformer;

use AppBundle\Entity\Equipment;
use League\Fractal\TransformerAbstract;

class EquipmentTransformer extends TransformerAbstract
{
    /**
     * @return array
     */
    public function transform(Equipment $e)
    {
        return [
            'id' => $e->getId(),
            'name' => $e->getName()
        ];
    }
}
