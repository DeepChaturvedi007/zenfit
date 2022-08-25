<?php

namespace AppBundle\Repository;

use AppBundle\Entity\ActivityLog as Entity;
use AppBundle\Entity\Client;
use AppBundle\Entity\Event;
use AppBundle\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Entity|null find($id, $lockMode = null, $lockVersion = null)
 * @method Entity|null findOneBy(array $criteria, array $orderBy = null)
 * @extends ServiceEntityRepository<Entity>
 */
class ActivityLogRepository extends ServiceEntityRepository
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

    /**
     * @param User $user
     * @param int $page
     * @param int $limit
     * @return \Doctrine\ORM\Tools\Pagination\Paginator
     */
    public function getAllByUser(User $user, $page = 1, $limit = 10)
    {
        $qb = $this->createQueryBuilder('a');
        $dql = $qb
            ->select(['a.id', 'c.id as client_id', 'c.name as client_name', 'c.email as client_email', 'e.name as event_name', 'a.date', 'a.seen'])
            ->innerJoin('a.event', 'e', Join::WITH, 'e.id = a.event')
            ->innerJoin('a.client', 'c', Join::WITH, 'c.id = a.client')
            ->where('a.user = :user')
            ->orWhere('c.user = :user')
            ->andWhere($qb->expr()->isNotNull('a.event'))
            ->orderBy('a.date', 'DESC')
            ->setParameter('user', $user->getId())
            ->getQuery();

        return $this->paginate($dql, $page, $limit);
    }

    public function getUnseenCountByUser(User $user)
    {
        $qb = $this->createQueryBuilder('a');

        return $qb
            ->select('COUNT(a.id)')
            ->innerJoin('a.event', 'e', Join::WITH, 'e.id = a.event')
            ->innerJoin('a.client', 'c', Join::WITH, 'c.id = a.client')
            ->where('a.user = :user')
            ->orWhere('c.user = :user')
            ->andWhere($qb->expr()->isNotNull('a.event'))
            ->andWhere('a.seen = 0')
            ->orderBy('a.date', 'DESC')
            ->setParameter('user', $user->getId())
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @param \Doctrine\ORM\Query|\Doctrine\ORM\QueryBuilder $dql A Doctrine ORM query or query builder.
     * @param int $page  Current page (defaults to 1)
     * @param int $limit The total number per page (defaults to 5)
     * @return \Doctrine\ORM\Tools\Pagination\Paginator
     */
    public function paginate($dql, $page = 1, $limit = 5)
    {
        $paginator = new Paginator($dql);
        $paginator->setUseOutputWalkers(false);

        $paginator
            ->getQuery()
            ->setFirstResult($limit * ($page - 1))
            ->setMaxResults($limit);

        return $paginator;
    }

    public function getByEventAndDate(?Client $client, ?User $user, Event $event, \DateTime $date)
    {
        $qb = $this->createQueryBuilder('al')
          ->where('al.date > :date_start')
          ->andWhere('al.date < :date_end')
          ->andWhere('al.event = :event');

        if($client) {
          $qb = $qb
            ->andWhere('al.client = :client')
            ->setParameter('client', $client);
        } else {
          $qb = $qb
            ->andWhere('al.user = :user')
            ->setParameter('user', $user);
        }

        $qb = $qb
          ->setParameter('date_start', $date->format('Y-m-d 00:00:00'))
          ->setParameter('date_end',   $date->format('Y-m-d 23:59:59'))
          ->setParameter('event', $event)
          ->setMaxResults(1)
          ->getQuery();

      return $qb->getOneOrNullResult();
    }
}
