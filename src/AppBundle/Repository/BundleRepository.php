<?php

namespace AppBundle\Repository;

use AppBundle\Entity\User;
use AppBundle\Entity\Bundle as Entity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Entity|null find($id, $lockMode = null, $lockVersion = null)
 * @method Entity|null findOneBy(array $criteria, array $orderBy = null)
 * @extends ServiceEntityRepository<Entity>
 */
class BundleRepository extends ServiceEntityRepository
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
     * @param User
     */
    public function findPlanBundlesByUser(User $user)
    {
        $qb = $this->createQueryBuilder('b');

        return $qb
            ->where('b.user = :user')
            ->andWhere($qb->expr()->in('b.type', [Entity::TYPE_WORKOUT_PLAN, Entity::TYPE_MEAL_PLAN, Entity::TYPE_BOTH]))
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }
}
