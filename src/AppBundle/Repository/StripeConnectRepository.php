<?php

namespace AppBundle\Repository;

use AppBundle\Entity\ClientStripe;
use AppBundle\Entity\StripeConnect;
use AppBundle\Entity\StripeConnect as Entity;
use AppBundle\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Entity|null find($id, $lockMode = null, $lockVersion = null)
 * @method Entity|null findOneBy(array $criteria, array $orderBy = null)
 * @extends ServiceEntityRepository<Entity>
 */
class StripeConnectRepository extends ServiceEntityRepository
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
    public function getConnectFeesBetweenDates(\DateTime $start, \DateTime $end, ?User $user = null): array
    {
        $qb = $this
            ->createQueryBuilder('sc')
            ->select('sc.id, sc.createdAt date, sc.amount, p.applicationFee application_fee, sp.name sales_person_name, sc.currency, sc.type')
            ->andWhere('sc.createdAt BETWEEN :start AND :end');

        if ($user !== null) {
            $qb
                ->andWhere('sc.user = :user')
                ->setParameter('user', $user);
        }

        $qb
            ->leftJoin('sc.paymentsLog', 'pl')
            ->leftJoin(ClientStripe::class, 'clientStripe', Join::WITH, 'clientStripe.stripeCustomer = pl.customer')
            ->leftJoin('clientStripe.payment', 'p')
            ->leftJoin('p.salesPerson', 'sp')
            ->setParameter('start', $start->format('Y-m-d'))
            ->setParameter('end', $end->format('Y-m-d 23:59:59'));

        $feePercentage = 0;
        if ($user !== null) {
            $userStripe = $user->getUserStripe();
            if ($userStripe !== null) {
                $feePercentage = $userStripe->getFeePercentage() ?? 0;
            }
        }

        $collection = collect($qb->getQuery()->getResult())
            ->map(function(array $fee) use ($feePercentage) {

                $salesPersonPercentage = 0;
                $fullAmount = 0;
                $salesPersonCommission = 0;

                if ($fee['application_fee'] > 0) {
                    $salesPersonPercentage = ($fee['application_fee'] - $feePercentage) / 100;
                    $fullAmount = (100 / $fee['application_fee'] * $fee['amount']);
                    $salesPersonCommission = $salesPersonPercentage * $fullAmount;
                }

                return [
                    'id' => $fee['id'],
                    'date' => $fee['date']->format('Y-m-d'),
                    'amount' => $fee['amount'],
                    'salesPersonName' => $fee['sales_person_name'] ?? 'Other',
                    'applicationFee' => $fee['application_fee'],
                    'currency' => $fee['currency'],
                    'salesPersonCommission' => $salesPersonCommission,
                    'type' => Entity::TYPES[$fee['type']]
                ];
            })
            ->groupBy(['type', 'salesPersonName']);

        return $collection->toArray();
    }
}
