<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Client;
use AppBundle\Entity\VideoClient as Entity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;
use VideoBundle\Transformer\VideoClientTransformer;

/**
 * @method Entity|null find($id, $lockMode = null, $lockVersion = null)
 * @method Entity|null findOneBy(array $criteria, array $orderBy = null)
 * @extends ServiceEntityRepository<Entity>
 */
class VideoClientRepository extends ServiceEntityRepository
{
    /** @var class-string<Entity> */
    protected $_entityName = Entity::class;
    private VideoClientTransformer $videoClientTransformer;

    public function __construct(ManagerRegistry $registry, VideoClientTransformer $videoClientTransformer)
    {
        /** @var EntityManager $em */
        $em = $registry->getManagerForClass($this->_entityName);
        $this->_em = $em;

        $this->_class = $this->_em->getClassMetadata($this->_entityName);
        $this->videoClientTransformer = $videoClientTransformer;

        parent::__construct($registry, $this->_entityName);
    }

    /**
     * @return array<mixed>
     */
    public function findByClient(Client $client): array
    {
        $qb = $this
            ->createQueryBuilder('vc')
            ->join('vc.client','c')
            ->join('vc.video','v')
            ->where('c.id = :client')
            ->andWhere('vc.deleted = 0')
            ->setParameter('client', $client)
            ->orderBy('vc.id', 'DESC');

        return collect($qb->getQuery()->getResult())
            ->map(function(Entity $vc) {
                return $this->videoClientTransformer->transform($vc);
            })->toArray();
    }

    /**
     * @param Client $client
     * @return mixed
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getNewVideosByClient(Client $client)
    {
        return $this
            ->createQueryBuilder('vc')
            ->select('COUNT(vc.id)')
            ->join('vc.client', 'c')
            ->join('vc.video','v')
            ->where('c.id = :client')
            ->andWhere('vc.deleted = 0')
            ->andWhere('vc.isNew = 1')
            ->setParameter('client', $client)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function markAsSeenByClient(Client $client): void
    {
        $this
            ->createQueryBuilder('vc')
            ->update()
            ->set('vc.isNew', ':isNew')
            ->where('vc.client = :client')
            ->andWhere('vc.isNew = 1')
            ->setParameters([
                'isNew' => false,
                'client' => $client
            ])
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @param array<Client> $clients
     * @return array<mixed>
     */
    public function getCountByClients(array $clients): array
    {
        return $this->createQueryBuilder('vc')
            ->select('count(vc.id) as count, IDENTITY(vc.client) as client_id')
            ->andWhere('vc.client in (:clients)')
            ->andWhere('vc.deleted = 0')
            ->setParameter('clients', $clients)
            ->groupBy('vc.client')
            ->getQuery()
            ->getArrayResult();
    }
}
