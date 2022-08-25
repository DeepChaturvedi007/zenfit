<?php

namespace AppBundle\Repository;

use AppBundle\Entity\BodyProgress as Entity;
use AppBundle\Entity\Client;
use AppBundle\Entity\ClientImage;
use AppBundle\Entity\ProgressFeedback;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Entity|null find($id, $lockMode = null, $lockVersion = null)
 * @method Entity|null findOneBy(array $criteria, array $orderBy = null)
 * @extends ServiceEntityRepository<Entity>
 */
class BodyProgressRepository extends ServiceEntityRepository
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

    public function getByClientAndDate(Client $client, \DateTime $date): ?Entity
    {
        $qb = $this->createQueryBuilder('bp');
        $result = $qb
            ->where('bp.client = :client')
            ->andWhere('bp.date >= :date_start')
            ->andWhere('bp.date <= :date_end')
            ->setParameters([
                'client' => $client,
                'date_start' => $date->format('Y-m-d 00:00:00'),
                'date_end' => $date->format('Y-m-d 23:59:59'),
            ])
            ->getQuery()
            ->getOneOrNullResult();

        return $result;
    }

    /**
     * @param Client $client
     * @return Entity
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getLastWeightByClient(Client $client)
    {
        return $this
            ->createQueryBuilder('bp')
            ->where('bp.client = :client')
            ->andWhere('bp.weight > 0')
            ->orderBy('bp.date', 'DESC')
            ->setParameter('client', $client)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param Client $client
     * @param \DateTime $date
     * @return Entity
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getEntryByClientAndDate(Client $client, \DateTime $date)
    {
        return $this
            ->createQueryBuilder('bp')
            ->where('bp.client = :client')
            ->andWhere('bp.date > :date_start')
            ->andWhere('bp.date < :date_end')
            ->orderBy('bp.date', 'DESC')
            ->setParameter('client', $client)
            ->setParameter('date_start', $date->format('Y-m-d 00:00:00'))
            ->setParameter('date_end', $date->format('Y-m-d 23:59:59'))
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param Client $client
     * @param ?int $limit
     * @param ?int $offset
     * @param string $order
     *
     * @return array
     */
    public function getEntriesByClient(Client $client, $limit = null, $offset = null, $order = 'ASC', $excludeNullFields = null): array
    {
        $fields = [];

        if ($excludeNullFields && $excludeNullFields === 'weight') {
            $fields = ['weight','fat'];
        }

        if ($excludeNullFields && $excludeNullFields === 'circumference') {
            $fields = [
                'chest', 'waist', 'hips', 'glutes',
                'leftArm', 'rightArm', 'rightThigh', 'leftThigh', 'leftCalf', 'rightCalf'
            ];
        }

        $qb = $this
            ->createQueryBuilder('bp')
            ->where('bp.client = :client');

        //if we should exclude null fields
        if ($excludeNullFields) {
            $orStatements = $qb->expr()->orX();
            foreach ($fields as $field) {
                $orStatements->add(
                    $qb->expr()->isNotNull("bp.$field")
                );
            }
            $qb->andWhere($orStatements);
        }

        $qb = $qb
            ->setParameter('client', $client)
            ->orderBy('bp.date', $order);

        if (null !== $limit) {
            $qb->setMaxResults($limit);
        }

        if (null !== $offset) {
            $qb->setFirstResult($offset);
        }

        return $qb
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * @param Client $client
     *
     * @return mixed
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function countByClient(Client $client)
    {
        $qb = $this->createQueryBuilder('bp');

        return $qb
            ->select('COUNT(bp.id)')
            ->where('bp.client = :client')
            ->setParameter('client', $client)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @param Client $client
     */
    public function getProgressByClient(Client $client)
    {
        //prepare images
        $images = $this
            ->getEntityManager()
            ->getRepository(ClientImage::class)
            ->findByClient($client);

        $images = collect($images)
            ->map(function($image) {
                return [
                    'id' => $image['id'],
                    'name' => $image['name'],
                    'date' => $image['date']->format('Y-m-d')
                ];
            })
            ->groupBy('date');

        //prepare checkins
        $checkIns = $this
            ->getEntityManager()
            ->getRepository(ProgressFeedback::class)
            ->getEntriesByClient($client);

        $checkIns = collect($checkIns)
            ->map(function($checkIn) {
                return [
                    'id' => $checkIn['id'],
                    'content' => $checkIn['content'],
                    'date' => $checkIn['createdAt']->format('Y-m-d')
                ];
            })
            ->groupBy('date');


        //prepare entries
        $entries = $this
            ->getEntriesByClient($client, null, null, 'ASC', 'weight');

        $entries = collect($entries)
            ->map(function($bp) use ($images, $checkIns) {
                $date = $bp['date']->format('Y-m-d');
                return [
                    'weight' => $bp['weight'],
                    'date' => $date,
                    'images' => $images->get($date),
                    'checkIns' => $checkIns->get($date)
                ];
            })
            ->groupBy('date');

        return $entries;
    }

}
