<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Client;
use AppBundle\Entity\Document;
use AppBundle\Entity\DocumentClient as Entity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;
use AppBundle\Transformer\DocumentTransformer;

/**
 * @method Entity|null find($id, $lockMode = null, $lockVersion = null)
 * @method Entity|null findOneBy(array $criteria, array $orderBy = null)
 * @extends ServiceEntityRepository<Entity>
 */
class DocumentClientRepository extends ServiceEntityRepository
{
    /** @var class-string<Entity> */
    protected $_entityName = Entity::class;
    private DocumentTransformer $documentTransformer;

    public function __construct(ManagerRegistry $registry, DocumentTransformer $documentTransformer)
    {
        /** @var EntityManager $em */
        $em = $registry->getManagerForClass($this->_entityName);
        $this->_em = $em;

        $this->_class = $this->_em->getClassMetadata($this->_entityName);
        $this->documentTransformer = $documentTransformer;

        parent::__construct($registry, $this->_entityName);
    }

    /**
     * @return array<mixed>
     */
    public function findByClient(Client $client): array
    {
        $qb = $this
            ->createQueryBuilder('dc')
            ->join('dc.client','c')
            ->join('dc.document','d')
            ->where('c.id = :client')
            ->setParameter('client', $client)
            ->orderBy('dc.id', 'DESC');

        return collect($qb->getQuery()->getResult())
            ->map(function(Entity $dc) {
                return $this->documentTransformer->transform($dc->getDocument());
            })->toArray();
    }

    /**
     * @param Document $document
     * @return mixed
     */
    public function findByDocument(Document $document)
    {
        return $this
            ->createQueryBuilder('dc')
            ->join('dc.client','c')
            ->join('dc.document','d')
            ->where('d.id = :document')
            ->andWhere('d.deleted = 0')
            ->setParameter('document', $document)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Client $client
     * @return mixed
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function countByClient(Client $client)
    {
        return $this
            ->createQueryBuilder('dc')
            ->select('COUNT(dc.id)')
            ->join('dc.client', 'c')
            ->join('dc.document','d')
            ->where('c.id = :client')
            ->setParameter('client', $client)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @param array<Client> $clients
     * @return array<mixed>
     */
    public function getCountByClients(array $clients): array
    {
        return $this->createQueryBuilder('dc')
            ->select('count(dc.id) as count, IDENTITY(dc.client) as client_id')
            ->andWhere('dc.client in (:clients)')
            ->setParameter('clients', $clients)
            ->groupBy('dc.client')
            ->getQuery()
            ->getArrayResult();
    }
}
