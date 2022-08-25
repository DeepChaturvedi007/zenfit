<?php

namespace AppBundle\Repository;

use AppBundle\Entity\UserSubscription;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method UserSubscription|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserSubscription|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserSubscription[]    findAll()
 * @method UserSubscription[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserSubscriptionRepository extends ServiceEntityRepository
{
    /** @var class-string<UserSubscription> */
    protected $_entityName = UserSubscription::class;

    public function __construct(ManagerRegistry $registry)
    {
        /** @var EntityManager $em */
        $em = $registry->getManagerForClass($this->_entityName);
        $this->_em = $em;

        $this->_class = $this->_em->getClassMetadata($this->_entityName);

        parent::__construct($registry, $this->_entityName);
    }

    /** @return array<UserSubscription> */
    public function getAllSubscriptions(): array
    {
        return $this
            ->createQueryBuilder('us')
            ->where('us.stripeCustomer IS NOT NULL')
            ->andWhere('us.stripeSubscription IS NOT NULL')
            ->getQuery()
            ->getResult();
    }

    public function persist(UserSubscription $entity): void
    {
        $this->_em->persist($entity);
    }

    public function flush(): void
    {
        $this->_em->flush();
    }
}
