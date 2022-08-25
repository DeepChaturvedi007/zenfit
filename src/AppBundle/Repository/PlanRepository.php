<?php

namespace AppBundle\Repository;

use AppBundle\Entity\User;
use AppBundle\Entity\Plan as Entity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Entity|null find($id, $lockMode = null, $lockVersion = null)
 * @method Entity|null findOneBy(array $criteria, array $orderBy = null)
 * @extends ServiceEntityRepository<Entity>
 */
class PlanRepository extends ServiceEntityRepository
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
     * @param int|null $limit
     * @param int|null $offset
     * @param bool|null $deleted
     * @return mixed
     */
    public function findAllPlansByUser(User $user, $limit = null, $offset = null, $deleted = null)
    {
        $qb = $this
            ->createQueryBuilder('p')
            ->join('p.client', 'c')
            ->where('c.user = :user')
            ->andWhere('p.deleted = :deleted')
            ->setParameter('user', $user)
            ->orderBy('p.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset);

        if($deleted) {
            $qb->setParameter('deleted', 1);
        } else {
            $qb->setParameter('deleted', 0);
        }
        return $qb
            ->getQuery()
            ->getResult();
    }

}
