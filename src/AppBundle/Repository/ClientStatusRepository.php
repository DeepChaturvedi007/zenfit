<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Client;
use AppBundle\Entity\ClientStatus as Entity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Entity|null find($id, $lockMode = null, $lockVersion = null)
 * @method Entity|null findOneBy(array $criteria, array $orderBy = null)
 * @extends ServiceEntityRepository<Entity>
 */
class ClientStatusRepository extends ServiceEntityRepository
{
    /** @var class-string<Entity> */
    protected $_entityName = Entity::class;

    public function __construct(ManagerRegistry $registry)
    {
        /** @var EntityManager $em */
        $em = $registry->getManagerForClass($this->_entityName);
        $this->_em = $em;

        $this->_class = $this->_em->getClassMetadata($this->_entityName);

        parent::__construct($registry, $this->_entityName);
    }

    /** @return array<mixed> */
    public function getStatusByClient(Client $client): array
    {
        $qb = $this
            ->createQueryBuilder('cs')
            ->select([
              'e.id AS event_id',
              'e.name AS event_name',
              'e.title',
              'e.priority',
              'cs.id',
              'cs.date',
              'cs.resolved',
              'cs.resolvedBy AS resolved_by'
            ])
            ->join('cs.event', 'e')
            ->where('cs.client = :client')
            ->addOrderBy('e.priority', 'DESC')
            ->addOrderBy('cs.id', 'DESC')
            ->setParameter('client', $client);

        return $qb->getQuery()->getArrayResult();
    }

    public function getEventByClient(Client $client, $event)
    {
        $qb = $this->createQueryBuilder('cs');

        $entry = $qb
            ->join('cs.event', 'event')
            ->where('cs.client = :client')
            ->andWhere('cs.resolved = 0')
            ->andWhere('event.name = :event')
            ->setParameter('client', $client)
            ->setParameter('event', $event)
            ->orderBy('cs.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if($entry) {
            return ['exists' => true, 'id' => $entry->getId(), 'date' => $entry->getDate()];
        }

        return ['exists' => false];
    }

    public function getDaysSinceLastEvent(Client $client, $event)
    {
        $qb = $this->createQueryBuilder('cs');

        $entry = $qb
            ->join('cs.event', 'event')
            ->where('cs.client = :client')
            ->andWhere('event.name = :event')
            ->setParameter('client', $client)
            ->setParameter('event', $event)
            ->addOrderBy('cs.id','DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if($entry) {
            $today = new \DateTime();
            $date = $entry->getDate();
            $diff = $date->diff($today);

            return (int) $diff->format('%r%a');
        }

        return true;
    }

}
