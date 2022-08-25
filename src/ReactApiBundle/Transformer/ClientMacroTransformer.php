<?php
namespace ReactApiBundle\Transformer;

use AppBundle\Entity\ClientMacro;
use League\Fractal\TransformerAbstract;

class ClientMacroTransformer extends TransformerAbstract
{
    /**
     * @param ClientMacro $clientMacro
     * @return array
     */
    public function transform($clientMacro)
    {
        return [
            'id' => $clientMacro->getId(),
            'protein' => $clientMacro->getProtein(),
            'carbs' => $clientMacro->getCarbs(),
            'kcal' => $clientMacro->getKcal(),
            'fat' => $clientMacro->getFat(),
            'date' => $clientMacro->getDate()->format('Y-m-d'),
        ];
    }
}
