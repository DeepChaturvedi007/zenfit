<?php

namespace AppBundle\Repository;

use AppBundle\Entity\WorkoutDay;
use AppBundle\Entity\WorkoutPlan;
use AppBundle\Entity\WorkoutDay as Entity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Entity|null find($id, $lockMode = null, $lockVersion = null)
 * @method Entity|null findOneBy(array $criteria, array $orderBy = null)
 * @extends ServiceEntityRepository<Entity>
 */
class WorkoutDayRepository extends ServiceEntityRepository
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

    public function getByPlanIds(array $ids)
    {
        $qb = $this->createQueryBuilder('wd');

        return $qb
            ->where($qb->expr()->in('wd.workoutPlan', ':ids'))
            ->setParameter('ids', $ids)
            ->orderBy('wd.order', 'asc')
            ->getQuery()
            ->setCacheable(false)
            ->useQueryCache(false)
            ->getResult();
    }

    /**
     * @param WorkoutPlan $plan
     * @return int
     */
    public function getLastOrderByPlan(WorkoutPlan $plan)
    {
        $qb = $this->createQueryBuilder('wd');

        return $qb
            ->select('MAX(wd.order)')
            ->where('wd.workoutPlan = :plan')
            ->setParameter('plan', $plan)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getByIdAndPlan(int $id, WorkoutPlan $plan): ?WorkoutDay
    {
        $qb = $this->createQueryBuilder('wd');

        return $qb
            ->where('wd.id = :id')
            ->andWhere('wd.workoutPlan = :plan')
            ->setParameters([
                'id' => $id,
                'plan' => $plan,
            ])
            ->getQuery()
            ->getSingleResult();
    }
}
