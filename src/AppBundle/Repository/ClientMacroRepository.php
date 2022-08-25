<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Client;
use AppBundle\Entity\ClientMacro as Entity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Entity|null find($id, $lockMode = null, $lockVersion = null)
 * @method Entity|null findOneBy(array $criteria, array $orderBy = null)
 * @extends ServiceEntityRepository<Entity>
 */
class ClientMacroRepository extends ServiceEntityRepository
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

    public function findByClient(Client $client)
    {
        return $this->createQueryBuilder('cm')
            ->where('cm.client = :client')
            ->orderBy('cm.date','ASC')
            ->setParameter('client',$client)
            ->getQuery()
            ->getResult();
    }

    public function findByClientAndDate(Client $client, $date)
    {
        return $this->createQueryBuilder('cm')
            ->where('cm.client = :client')
            ->andWhere('cm.date = :date')
            ->setParameter('client',$client)
            ->setParameter('date', $date->format('Y-m-d'))
            ->getQuery()
            ->getOneOrNullResult();
    }

}
