<?php
namespace AppBundle\Transformer;

use AppBundle\Entity\ClientReminder;
use League\Fractal\TransformerAbstract;

class ClientReminderTransformer extends TransformerAbstract
{
    /**
     * @return array
     */
    public function transform(ClientReminder $cm)
    {
        return [
            'id' => $cm->getId(),
            'title' => $cm->getTitle(),
            'resolved' => $cm->getResolved(),
            'dueDate' => $cm->getDueDate()
        ];
    }
}
