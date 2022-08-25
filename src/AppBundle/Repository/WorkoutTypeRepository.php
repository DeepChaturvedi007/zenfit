<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Exercise;
use AppBundle\Entity\WorkoutType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method WorkoutType|null find($id, $lockMode = null, $lockVersion = null)
 * @method WorkoutType|null findOneBy(array $criteria, array $orderBy = null)
 * @method WorkoutType[]    findAll()
 * @method WorkoutType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WorkoutTypeRepository extends ServiceEntityRepository
{
    /** @var class-string<WorkoutType> */
    protected $_entityName = WorkoutType::class;

    public function __construct(ManagerRegistry $registry)
    {
        /** @var EntityManager $em */
        $em = $registry->getManagerForClass($this->_entityName);
        $this->_em = $em;

        $this->_class = $this->_em->getClassMetadata($this->_entityName);

        parent::__construct($registry, $this->_entityName);
    }

    public function persist(WorkoutType $entity): void
    {
        $this->_em->persist($entity);
    }

    public function flush(): void
    {
        $this->_em->flush();
    }
}
