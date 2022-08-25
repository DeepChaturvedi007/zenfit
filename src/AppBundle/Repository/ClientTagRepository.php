<?php

namespace AppBundle\Repository;

use AppBundle\Entity\User;
use AppBundle\Entity\ClientTag as Entity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Entity|null find($id, $lockMode = null, $lockVersion = null)
 * @method Entity|null findOneBy(array $criteria, array $orderBy = null)
 * @extends ServiceEntityRepository<Entity>
 */
class ClientTagRepository extends ServiceEntityRepository
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

    public function getAllTagsByUser(User $user)
    {
        return $this->createQueryBuilder('ct')
            ->select(['ct.id, ct.title, c.id as client'])
            ->join('ct.client', 'c')
            ->where('c.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * @param array $clientIds
     * @return mixed
     */
    public function getAllByClientIds(array $clientIds)
    {
        $qb = $this->createQueryBuilder('ct');

        return $qb
            ->select('ct.id, ct.title, IDENTITY(ct.client) AS client_id')
            ->where($qb->expr()->in('ct.client', ':tags'))
            ->setParameter('tags', $clientIds)
            ->getQuery()
            ->getArrayResult();
    }
}
