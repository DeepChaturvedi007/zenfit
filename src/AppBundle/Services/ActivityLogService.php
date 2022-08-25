<?php

namespace AppBundle\Services;

use AppBundle\Entity\ActivityLog;
use AppBundle\Entity\Client;
use AppBundle\Entity\Event;
use Doctrine\ORM\EntityManagerInterface;

class ActivityLogService
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function setActivitySeen($event, Client $client): void
    {
        $em = $this->em;
        $eventEntity = $this->em
            ->getRepository(Event::class)
            ->findOneBy(['name' => $event]);

        $entities = $this->em
            ->getRepository(ActivityLog::class)
            ->findBy(['event' => $eventEntity, 'client' => $client]);

        foreach($entities as $entity) {
          $entity->setSeen(true);
        }

        $em->flush();

    }
}
