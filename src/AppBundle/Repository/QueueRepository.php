<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Queue;
use AppBundle\Entity\Client;
use AppBundle\Entity\User;
use AppBundle\Entity\Queue as Entity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Entity|null find($id, $lockMode = null, $lockVersion = null)
 * @method Entity|null findOneBy(array $criteria, array $orderBy = null)
 * @extends ServiceEntityRepository<Entity>
 */
class QueueRepository extends ServiceEntityRepository
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

    public function getAllSendInvitations($ids)
    {
        $now = new \DateTime();
        $now->modify('-1 hour');

        $qb = $this->createQueryBuilder('q');
        $qb2 = $this->createQueryBuilder('q2');

        $result = $qb
            ->select([
                'q.id AS id',
                'q.createdAt',
                'client.id AS clientId',
                'q.datakey',
                'q.type',
                'q.survey',
                'q.status',
                'IDENTITY(q.payment) as payment'
            ])
            ->leftJoin('q.client', 'client')
            ->where($qb->expr()->in('q.client', $ids))
            ->andWhere($qb->expr()->eq('q.type', Queue::TYPE_CLIENT_EMAIL))
            ->andWhere($qb->expr()->in(
                'q.id',
                $qb2
                    ->select('max(q2.id)')
                    ->where($qb->expr()->eq('q2.type', Queue::TYPE_CLIENT_EMAIL))
                    ->groupBy('q2.client')
                    ->getDQL()
            ))
            ->orderBy('clientId', 'ASC')
            ->getQuery()
            ->getResult();

        return collect($result)
            ->keyBy(function ($item) {
                return $item['clientId'];
            })
            ->map(function ($item) use ($now) {
                $created = $item['createdAt'] ?: clone $now;
                $item['delay'] =
                    $created->getTimestamp() <= $now->getTimestamp()
                        ? null
                        : $created->diff($now)->format('%i');

                return $item;
            });
    }

    public function getClientQuestionnaire(Client $client)
    {
        $qb = $this->createQueryBuilder('q');

        return $qb
            ->select([
                'q.datakey'
            ])
            ->where('q.client = :client')
            ->andWhere($qb->expr()->eq('q.type', Queue::TYPE_CLIENT_EMAIL))
            ->andWhere($qb->expr()->eq('q.survey', 1))
            ->setParameter('client', $client->getId())
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param User $user
     * @param array $types
     * @param array $statuses
     *
     * @return object[]
     */
    public function findAllByUser(User $user, array $types = [], array $statuses = [])
    {
        $qb = $this->createQueryBuilder('q');

        $qb
            ->where('q.user = :user')
            ->setParameter('user', $user);

        if (count($types) > 0) {
            $qb->andWhere($qb->expr()->in('q.type', $types));
        }

        if (count($statuses) > 0) {
            $qb->andWhere($qb->expr()->in('q.status', $statuses));
        }

        return $qb
            ->getQuery()
            ->getResult();
    }

    public function findOneByDatakey($datakey)
    {
        return $this->findOneBy([
            'datakey' => $datakey,
        ]);
    }

    public function findLatestInvitationByClient(Client $client)
    {
        return $this->findOneBy([
            'client' => $client,
            'type' => Queue::TYPE_CLIENT_EMAIL
        ], ['id' => 'DESC']);
    }
}
