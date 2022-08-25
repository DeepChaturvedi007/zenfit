<?php
namespace AppBundle\Transformer;

use AppBundle\Entity\ClientStatus;
use AppBundle\Entity\Event;
use League\Fractal\TransformerAbstract;

class ClientStatusTransformer extends TransformerAbstract
{
    /**
     * @return array
     */
    public function transform(ClientStatus $cs, Event $event)
    {
        return [
            'id' => $cs->getId(),
            'client' => [
                'id' => $cs->getClient()->getId(),
                'name' => $cs->getClient()->getName()
            ],
            'event' => [
                'id' => $event->getId(),
                'name' => $event->getName()
            ]
        ];
    }
}
