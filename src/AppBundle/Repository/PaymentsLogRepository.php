<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Client;
use AppBundle\Entity\User;
use AppBundle\Entity\PaymentsLog;
use Carbon\Carbon;
use DateTimeInterface;
use Doctrine\ORM\Query\Expr\Join;
use AppBundle\Entity\PaymentsLog as Entity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Entity|null find($id, $lockMode = null, $lockVersion = null)
 * @method Entity|null findOneBy(array $criteria, array $orderBy = null)
 * @extends ServiceEntityRepository<Entity>
 */
class PaymentsLogRepository extends ServiceEntityRepository
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
     * @param Client $client
     * @return array
     */
    public function findByClient(Client $client)
    {
        return $this
            ->createQueryBuilder('pl')
            ->where('pl.client = :client')
            ->orderBy('pl.id','DESC')
            ->setParameter('client',$client)
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * @param User $user
     * @param DateTimeInterface $startDate
     * @param DateTimeInterface $endDate
     * @return mixed
     */
    public function getRevenueStreams(User $user, DateTimeInterface $startDate, DateTimeInterface $endDate)
    {
        $qb = $this->createQueryBuilder('pl');
        $qb
            ->select('pl.id', 'pl.amount, pl.currency, pl.createdAt', 'ct.title AS tag')
            ->leftJoin('pl.client', 'c', Join::WITH, 'pl.client = c.id')
            ->leftJoin('c.tags', 'ct', Join::WITH, 'pl.client = ct.client')
            ->where('c.user = :user')
            ->andWhere(
                $qb->expr()->orX('pl.type = :type_succeeded', 'pl.type = :type_charged', 'pl.type = :type_refunded')
            )
            ->andWhere('pl.amount IS NOT NULL')
            ->andWhere('pl.createdAt BETWEEN :start AND :end')
            ->setParameters([
                'user' => $user,
                'type_succeeded' => PaymentsLog::PAYMENT_SUCCEEDED,
                'type_charged' => PaymentsLog::CHARGE_SUCCEEDED,
                'type_refunded' => PaymentsLog::CHARGE_REFUNDED,
                'start' => $startDate,
                'end' => $endDate,
            ])
        ;
        $rows = $qb->getQuery()->getResult();

        $rows = collect($rows);
        $rows = $rows->groupBy('tag');

        $data = [];
        foreach ($rows as $k => $row) {
            $now = Carbon::now();
            $items = collect($row);
            $currentItems = $items->filter(function ($item) use ($now) {
                $startDate  = clone $now;
                $endDate    = clone $now;

                $startDate  = $startDate->startOfMonth();
                $endDate    = $endDate->endOfMonth();

                $checkDate  = Carbon::parse($item['createdAt']);

                return $startDate <= $checkDate && $checkDate <= $endDate;
            });

            $previousItems = $items->filter(function ($item) use ($now) {
                $startDate  = clone $now;
                $endDate    = clone $now;

                $startDate->subMonth();
                $endDate->subMonth();

                $startDate  = $startDate->startOfMonth();
                $endDate    = $endDate->endOfMonth();

                $checkDate  = Carbon::parse($item['createdAt']);

                return $startDate <= $checkDate && $checkDate <= $endDate;
            });

            $rows[$k] = [];
            $current = $currentItems->sum('amount');
            $previous = $previousItems->sum('amount');
            array_push($data, [
                'from' => $k,
                'thisMonth' =>  round($current, 2),
                'lastMonth' => round($previous, 2),
                'percentage' => round($previous > 0 ? (($current - $previous) * 100) / $previous : 100)
            ]);
        }
        return $data;
    }

    /**
     * @param User $user
     * @param DateTimeInterface $startDate
     * @param DateTimeInterface $endDate
     * @return mixed
     */
    public function findByUserAndDateRange(User $user, DateTimeInterface $startDate, DateTimeInterface $endDate)
    {
        $qb = $this->createQueryBuilder('pl');
        $qb
            ->select('pl.id', 'pl.amount, pl.currency, pl.type', 'pl.createdAt')
            ->leftJoin('pl.client', 'c', Join::WITH, 'pl.client = c.id')
            ->where('c.user = :user')
            ->andWhere(
                $qb->expr()->orX('pl.type = :type_succeeded', 'pl.type = :type_charged', 'pl.type = :type_refunded')
            )
            ->andWhere('pl.amount IS NOT NULL')
            ->andWhere('pl.createdAt BETWEEN :start AND :end')
            ->setParameters([
                'user' => $user,
                'type_succeeded' => PaymentsLog::PAYMENT_SUCCEEDED,
                'type_charged' => PaymentsLog::CHARGE_SUCCEEDED,
                'type_refunded' => PaymentsLog::CHARGE_REFUNDED,
                'start' => $startDate,
                'end' => $endDate,
            ])
        ;

        return $qb
            ->getQuery()
            ->getResult();
    }

    public function findByUser(User $user, $year = null, $month = null)
    {
        if ($month === null) {
            $month = (int) date('m');
        }

        if ($year === null) {
            $year = (int) date('Y');
        }

        $date = new \DateTimeImmutable("$year-$month-01T00:00:00");

        $qb = $this->createQueryBuilder('pl');

        return $qb
            ->join('pl.client', 'c', Join::WITH, 'pl.client = c.id')
            ->where('c.user = :user')
            ->andWhere($qb->expr()->orX('pl.type = :type_succeeded', 'pl.type = :type_charged', 'pl.type = :type_refunded'))
            ->andWhere('pl.createdAt >= :timestamp') //we get data from february 2019
            ->andWhere('pl.amount IS NOT NULL')
            ->setParameters([
              'user' => $user,
              'type_succeeded' => PaymentsLog::PAYMENT_SUCCEEDED,
              'type_charged' => PaymentsLog::CHARGE_SUCCEEDED,
              'type_refunded' => PaymentsLog::CHARGE_REFUNDED,
              'timestamp' => $date,
            ])
            ->getQuery()
            ->getResult();
    }
}
