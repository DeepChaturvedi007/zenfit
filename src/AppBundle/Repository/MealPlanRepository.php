<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Client;
use AppBundle\Entity\MasterMealPlan;
use AppBundle\Entity\MealPlan;
use AppBundle\Entity\MealPlan as Entity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Entity|null find($id, $lockMode = null, $lockVersion = null)
 * @method Entity|null findOneBy(array $criteria, array $orderBy = null)
 * @extends ServiceEntityRepository<Entity>
 */
class MealPlanRepository extends ServiceEntityRepository
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
     * @param MasterMealPlan $plan
     * @return int
     */
    public function getLastOrderByPlan(MasterMealPlan $plan)
    {
        $qb = $this->createQueryBuilder('mp');

        return $qb
            ->select('MAX(mp.order)')
            ->where('mp.masterMealPlan = :plan')
            ->setParameter('plan', $plan)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @param array $ids
     * @return MealPlan[]
     */
    public function getByIds(array $ids)
    {
        $qb = $this->createQueryBuilder('mp');

        return $qb
            ->where($qb->expr()->in('mp.id', $ids))
            ->andWhere($qb->expr()->eq('mp.deleted', 0))
            ->orderBy('mp.order', 'asc')
            ->getQuery()
            ->getResult();
    }

    public function getByClient(Client $client)
    {
        return $this->createQueryBuilder('mp')
            ->where('mp.client = :client')
            ->andWhere('mp.parent is NULL')
            ->setParameters([
                'client' => $client,
            ])
            ->orderBy('mp.order', 'asc')
            ->getQuery()
            ->getResult();
    }


    /**
     * @param MasterMealPlan $plan
     * @return MealPlan[]
     */
    public function getByMasterPlan(MasterMealPlan $plan)
    {
        return $this
            ->createQueryBuilder('mp')
            ->where('mp.masterMealPlan = :plan')
            ->andWhere('mp.parent is NULL')
            ->setParameters([
                'plan' => $plan,
            ])
            ->orderBy('mp.order', 'asc')
            ->getQuery()
            ->getResult();
    }

}
