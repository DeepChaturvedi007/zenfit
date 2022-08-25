<?php

namespace AppBundle\Repository;

use AppBundle\Entity\ClientStripe as Entity;
use AppBundle\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;
use Illuminate\Support\Collection as LaravelCollection;

/**
 * @method Entity|null find($id, $lockMode = null, $lockVersion = null)
 * @method Entity|null findOneBy(array $criteria, array $orderBy = null)
 * @extends ServiceEntityRepository<Entity>
 */
class ClientStripeRepository extends ServiceEntityRepository
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

    /** @return LaravelCollection<Entity> */
    public function getAllStripeDetailsByIds(array $ids): LaravelCollection
    {
        $qb = $this->createQueryBuilder('cs');
        $qb2 = $this->createQueryBuilder('cs2');
        $result = $qb
            ->where($qb->expr()->in('cs.client', $ids))
            ->andWhere($qb->expr()->in(
                'cs.id',
                $qb2
                    ->select('max(cs2.id)')
                    ->groupBy('cs2.client')
                    ->getDQL()
            ))
            ->getQuery()
            ->getResult();

        return collect($result)
            ->keyBy(function($clientStripe) {
                return $clientStripe->getClient()->getId();
            });
    }

    /** @return array<mixed> */
    public function getClientStripeMetricsByUser(User $user, \DateTime $start, \DateTime $end): array
    {
        $qb = $this
            ->createQueryBuilder('cs')
            ->leftJoin('cs.client', 'c')
            ->leftJoin('c.user', 'u')
            ->leftJoin('cs.payment', 'p')
            ->andWhere('cs.currentPeriodStart >= :start')
            ->setParameter('start', $start->getTimestamp())
            ->andWhere('cs.canceledAt <= :end')
            ->setParameter('start', $start->getTimestamp())
            ->setParameter('end', $end->getTimestamp())
            ->andWhere('u.id = :user')
            ->setParameter('user', $user)
            ->select('ROUND(AVG(DATEDIFF(FROM_UNIXTIME(cs.canceledAt), FROM_UNIXTIME(cs.currentPeriodStart)))/30, 1) as average_lifetime')
            ->addSelect('ROUND(AVG(p.recurringFee), 2) average_fee')
            ->addSelect('ROUND(AVG(p.upfrontFee), 2) upfront_fee')
        ;

        $qb->andWhere('cs.canceledAt is not null')
            ->andWhere('cs.canceled = 1')
            ->andWhere('cs.canceledAt != 0')
        ;

        $result = $qb->getQuery()->getResult();
        if (!is_array($result) || !array_key_exists(0, $result)) {
            throw new \RuntimeException();
        }

        $result = $result[0];

        return [
            'average_lifetime' => $result['average_lifetime'],
            'average_fee' => $result['average_fee'],
            'average_lifetime_value' => round($result['average_fee'] * $result['average_lifetime'] + $result['upfront_fee'], 2),
        ];
    }
}
