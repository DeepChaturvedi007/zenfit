<?php

namespace AppBundle\Repository;

use DateTime;
use AppBundle\Entity\SavedWorkout as Entity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Entity|null find($id, $lockMode = null, $lockVersion = null)
 * @method Entity|null findOneBy(array $criteria, array $orderBy = null)
 * @extends ServiceEntityRepository<Entity>
 */
class SavedWorkoutRepository extends ServiceEntityRepository
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
     * @param array<mixed> $ids
     * @return Entity[]
     */
    public function getByWorkoutDayIds(array $ids, ?string $fromDate, ?string $toDate): array
    {
        $qb = $this->createQueryBuilder('sw');
        $qb->where($qb->expr()->in('sw.workoutDay', ':ids'))
            ->setParameter('ids', $ids);

        if( $fromDate !== null || $toDate !== null ) {
            $qb->orderBy('sw.date','DESC');
        }

        if( $fromDate !== null ) {
            $qb
                ->andWhere($qb->expr()->gte('sw.date', ':fromDate'))
                ->setParameter('fromDate', new \DateTime($fromDate));
        }
        if( $toDate !== null ) {
            $qb
                ->andWhere($qb->expr()->lte('sw.date', ':toDate'))
                ->setParameter('toDate', new \DateTime($toDate));
        }

        /** @var Entity[] $result */
        $result = $qb
            ->getQuery()
            ->getResult();

        return $result;
    }
}
