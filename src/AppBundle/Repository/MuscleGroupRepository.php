<?php

namespace AppBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;
use ExerciseBundle\Transformer\MuscleGroupTransformer;
use AppBundle\Entity\MuscleGroup;

/**
 * @method MuscleGroup|null find($id, $lockMode = null, $lockVersion = null)
 * @method MuscleGroup|null findOneBy(array $criteria, array $orderBy = null)
 * @extends ServiceEntityRepository<MuscleGroup>
 */
class MuscleGroupRepository extends ServiceEntityRepository
{
    /** @var class-string<MuscleGroup> */
    protected $_entityName = MuscleGroup::class;

    public function __construct(ManagerRegistry $registry)
    {
        /** @var EntityManager $em */
        $em = $registry->getManagerForClass($this->_entityName);
        $this->_em = $em;

        $this->_class = $this->_em->getClassMetadata($this->_entityName);

        parent::__construct($registry, $this->_entityName);
    }

    public function getAllMuscleGroups()
    {
        $qb = $this
            ->createQueryBuilder('mg')
            ->orderBy('mg.name', 'ASC')
            ->getQuery()
            ->useQueryCache(true)
            ->enableResultCache();

        return collect($qb->getResult())
            ->map(function(MuscleGroup $mg) {
                return (new MuscleGroupTransformer())->transform($mg);
            });
    }


    public function persist(MuscleGroup $entity): void
    {
        $this->_em->persist($entity);
    }

    public function flush(): void
    {
        $this->_em->flush();
    }
}
