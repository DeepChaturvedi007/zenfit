<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Client;
use AppBundle\Entity\Payment;
use AppBundle\Transformer\PaymentTransformer;
use AppBundle\Entity\Payment as Entity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Entity|null find($id, $lockMode = null, $lockVersion = null)
 * @method Entity|null findOneBy(array $criteria, array $orderBy = null)
 * @extends ServiceEntityRepository<Entity>
 */
class PaymentRepository extends ServiceEntityRepository
{
    /** @var class-string<Entity> */
    protected $_entityName = Entity::class;
    private PaymentTransformer $paymentTransformer;

    public function __construct(ManagerRegistry $registry, PaymentTransformer $paymentTransformer)
    {
        /** @var EntityManager $em */
        $em = $registry->getManagerForClass($this->_entityName);
        $this->_em = $em;

        $this->_class = $this->_em->getClassMetadata($this->_entityName);
        $this->paymentTransformer = $paymentTransformer;

        parent::__construct($registry, $this->_entityName);
    }

    /** @return array<Entity> */
    public function getAllPaymentsByClient(Client $client): array
     {
        $qb = $this
            ->createQueryBuilder('p')
            ->where('p.client = :client')
            ->leftJoin('p.clientStripe', 'cs')
            ->andWhere('p.deleted = 0')
            ->orderBy('p.id', 'DESC')
            ->setParameter('client', $client);

        $payments = collect($qb->getQuery()->getResult())
            ->map(function(Payment $payment) {
                return $this->paymentTransformer->transform($payment);
            })
            ->unique('active');

        return $payments->toArray();
    }

    public function getClientLatestPaymentLink(Client $client)
    {
        $payment = $this->findOneBy([
            'client' => $client->getId(),
            'charged' => false
        ], [
            'id' => 'DESC'
        ]);

        return $payment;
    }

    /** @return array<Entity> */
    public function findUnpaidLinksByClient(Client $client): array
    {
        return $this
            ->createQueryBuilder('p')
            ->where('p.client = :client')
            ->andWhere('p.charged = 0')
            ->orderBy('p.id','DESC')
            ->setParameter('client',$client)
            ->getQuery()
            ->getResult();
    }

    public function getPaymentByClient(Client $client)
    {
        $payment = $this->findOneBy([
            'client' => $client->getId()
        ], [
            'id' => 'DESC'
        ]);

        return $payment;
    }

    public function getPaymentByDatakey(string $datakey, bool $charged = false): ?Payment
    {
        return $this->findOneBy([
            'datakey' => $datakey,
            'charged' => $charged
        ]);
    }
}
