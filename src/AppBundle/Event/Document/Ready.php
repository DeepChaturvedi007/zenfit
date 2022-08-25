<?php

namespace AppBundle\Event\Document;

use AppBundle\Entity\Document;
use Symfony\Contracts\EventDispatcher\Event;

class Ready extends Event
{

    const TYPE_MEAL_PLAN_DOWNLOAD = 'meal_plan_pdf.download';
    const TYPE_WORKOUT_PLAN_DOWNLOAD = 'workout_plan_pdf.download';

    public function __construct(protected Document $document)
    {
    }

    public function getDocument()
    {
        return $this->document;
    }
}
