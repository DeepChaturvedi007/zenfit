<?php

namespace AppBundle\Repository;

use AppBundle\Entity\MealProductWeight;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MealProductWeight|null find($id, $lockMode = null, $lockVersion = null)
 * @method MealProductWeight|null findOneBy(array $criteria, array $orderBy = null)
 * @method MealProductWeight[]    findAll()
 * @method MealProductWeight[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MealProductWeightRepository extends ServiceEntityRepository
{
    /** @var class-string<MealProductWeight> */
    protected $_entityName = MealProductWeight::class;

    public function __construct(ManagerRegistry $registry)
    {
        /** @var EntityManager $em */
        $em = $registry->getManagerForClass($this->_entityName);
        $this->_em = $em;

        $this->_class = $this->_em->getClassMetadata($this->_entityName);

        parent::__construct($registry, $this->_entityName);
    }

    public function persist(MealProductWeight $entity): void
    {
        $this->_em->persist($entity);
    }

    public function flush(): void
    {
        $this->_em->flush();
    }
}
