<?php

namespace AppBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;
use ExerciseBundle\Transformer\EquipmentTransformer;
use AppBundle\Entity\Equipment;

/**
 * @method Equipment|null find($id, $lockMode = null, $lockVersion = null)
 * @method Equipment|null findOneBy(array $criteria, array $orderBy = null)
 * @method Equipment[]    findAll()
 * @method Equipment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EquipmentRepository extends ServiceEntityRepository
{
    /** @var class-string<Equipment> */
    protected $_entityName = Equipment::class;

    public function __construct(ManagerRegistry $registry)
    {
        /** @var EntityManager $em */
        $em = $registry->getManagerForClass($this->_entityName);
        $this->_em = $em;

        $this->_class = $this->_em->getClassMetadata($this->_entityName);

        parent::__construct($registry, $this->_entityName);
    }

    public function getAllEquipment()
    {
        $qb = $this
            ->createQueryBuilder('e')
            ->orderBy('e.name', 'ASC')
            ->getQuery()
            ->useQueryCache(true)
            ->enableResultCache();

        return collect($qb->getResult())
            ->map(function(Equipment $e) {
                return (new EquipmentTransformer())->transform($e);
            });
    }

    public function persist(Equipment $entity): void
    {
        $this->_em->persist($entity);
    }

    public function flush(): void
    {
        $this->_em->flush();
    }
}
