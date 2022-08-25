<?php

namespace AppBundle\Repository;

use AppBundle\Entity\ExerciseType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ExerciseType|null find($id, $lockMode = null, $lockVersion = null)
 * @method ExerciseType|null findOneBy(array $criteria, array $orderBy = null)
 * @method ExerciseType[]    findAll()
 * @method ExerciseType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ExerciseTypeRepository extends ServiceEntityRepository
{
    /** @var class-string<ExerciseType> */
    protected $_entityName = ExerciseType::class;

    public function __construct(ManagerRegistry $registry)
    {
        /** @var EntityManager $em */
        $em = $registry->getManagerForClass($this->_entityName);
        $this->_em = $em;

        $this->_class = $this->_em->getClassMetadata($this->_entityName);

        parent::__construct($registry, $this->_entityName);
    }

    public function persist(ExerciseType $entity): void
    {
        $this->_em->persist($entity);
    }

    public function flush(): void
    {
        $this->_em->flush();
    }
}
