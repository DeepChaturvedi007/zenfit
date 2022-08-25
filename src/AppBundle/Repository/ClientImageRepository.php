<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Client;
use AppBundle\Entity\ClientImage as Entity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Entity|null find($id, $lockMode = null, $lockVersion = null)
 * @method Entity|null findOneBy(array $criteria, array $orderBy = null)
 * @extends ServiceEntityRepository<Entity>
 */
class ClientImageRepository extends ServiceEntityRepository
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

    /** @return array<Entity> */
    public function findByClient(Client $client, int $maxResults = 5, int $offset = 0, string $order = 'DESC'): array
    {
        /** @var array<Entity> $result */
        $result =  $this->createQueryBuilder('ci')
            ->where('ci.client = :client')
            ->andWhere('ci.deleted = 0')
            ->orderBy('ci.date', $order)
            ->setParameter('client', $client)
            ->setFirstResult($offset)
            ->setMaxResults($maxResults)
            ->getQuery()
            ->getArrayResult();

        return $result;
    }

    /** @return array<Entity> */
    public function getClientPhotos(Client $client, \DateTime $date = null): array
    {
        $qb = $this
            ->createQueryBuilder('ci')
            ->where('ci.client = :client')
            ->andWhere('ci.deleted = 0')
            ->setParameter('client', $client);

        if ($date) {
            $startDate = (clone $date)->setTime(0, 0 , 0, 0);
            $endDate = (clone $date)->setTime(23, 59 , 59, 0);

            $qb
                ->andWhere('ci.date >= :start_date')
                ->andWhere('ci.date <= :end_date')
                ->setParameter('start_date', $startDate)
                ->setParameter('end_date', $endDate);
        }

        /** @var array<Entity> $result */
        $result = $qb
            ->orderBy('ci.date', 'DESC')
            ->getQuery()
            ->getResult();

        return $result;
    }

}
