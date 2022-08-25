<?php

namespace AppBundle\Repository;

use DateTime;
use AppBundle\Entity\WorkoutPlan;
use AppBundle\Entity\TrackWorkout as Entity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;
use Illuminate\Support\Collection;

/**
 * @method Entity|null find($id, $lockMode = null, $lockVersion = null)
 * @method Entity|null findOneBy(array $criteria, array $orderBy = null)
 * @extends ServiceEntityRepository<Entity>
 */
class TrackWorkoutRepository extends ServiceEntityRepository
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
     * @param array $ids
     * @return mixed
     */
    public function getByWorkoutIds(array $ids)
    {
        $qb = $this->createQueryBuilder('tw');

        return $qb
            ->where($qb->expr()->in('tw.workout', ':ids'))
            ->andWhere('tw.deleted = 0')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param array $ids
     * @param array $dates
     * @return mixed
     */
    public function getByWorkoutIdsAndDates(array $ids, array $dates = [])
    {
        $qb = $this->createQueryBuilder('tw');

        return $qb
            ->where($qb->expr()->in('tw.date', $dates))
            ->andWhere($qb->expr()->in('tw.workout', $ids))
            ->andWhere('tw.deleted = 0')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param int $limit
     * @param int $offset
     * @param WorkoutPlan $plan|null
     * @return Collection<mixed>
     */
    public function getWorkoutPlanStats($limit, $offset, WorkoutPlan $plan): Collection
    {
        $qb = $this
            ->createQueryBuilder('tw')
            ->select('MAX(tw.date) as latest_tw_date, MIN(tw.date) as first_tw_date, MAX(CAST(REPLACE(tw.weight, \',\', \'.\') AS decimal (10,2))) as pr, e.name, e.id, w.id as workout_id, wd.id as workout_day_id')
            ->leftJoin('tw.workout', 'w')
            ->leftJoin('w.exercise', 'e')
            ->leftJoin('w.workoutDay', 'wd')
            ->leftJoin('wd.workoutPlan', 'wp')
            ->where('wp.id = :plan')
            ->andWhere('tw.deleted = false')
            ->andWhere('e.deleted = false')
            ->orderBy('e.id', 'ASC')
            ->groupBy('e.id')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->setParameter('plan', $plan);

        $stats = collect($qb->getQuery()->getResult());
        $stats = $stats->map(function($stat) {
            $stat = collect($stat);
            $firstTwDate = $stat->get('first_tw_date');
            $latestTwDate = $stat->get('latest_tw_date');
            $workoutId = $stat->get('workout_id');

            //get the first and last trackWorkout entry with highest weight
            $firstRows = $this->findBy([
                'workout' => $workoutId,
                'date' => new DateTime($firstTwDate)
            ]);

            $firstMax = collect($firstRows)
                ->map(function($row) {
                    return $row->getWeight();
                })->max();

            $latestRows = $this->findBy([
                'workout' => $workoutId,
                'date' => new DateTime($latestTwDate)
            ]);

            $latestMax = collect($latestRows)
                ->map(function($row) {
                    return $row->getWeight();
                })->max();

            $stat->forget(['first_tw_date','latest_tw_date']);
            $stat->put('first', $firstMax);
            $stat->put('latest', $latestMax);
            return $stat;
        });

        return $stats;
    }

    /**
     * @param WorkoutPlan $plan
     * @param int $exerciseId
     * @return mixed
     */
    public function getByExercise(WorkoutPlan $plan, $exerciseId)
    {
        $qb = $this
            ->createQueryBuilder('tw')
            ->select('e.name, tw.id, tw.date, tw.weight as weight, tw.reps')
            ->leftJoin('tw.workout', 'w')
            ->leftJoin('w.exercise', 'e', 'WITH', 'e.id = :exerciseId')
            ->leftJoin('w.workoutDay', 'wd')
            ->leftJoin('wd.workoutPlan', 'wp')
            ->where('wp.id = :plan')
            ->andWhere('tw.deleted = false')
            ->andWhere('e.deleted = false')
            ->setParameter('plan', $plan)
            ->setParameter('exerciseId', $exerciseId);

        return collect($qb->getQuery()->getResult())
            ->map(function($row) {
                return array_merge($row, ['date' => $row['date']->format('Y-m-d')]);
            })
            ->groupBy('date')
            ->map(function($row) {
                return $row->firstWhere('weight', $row->max('weight'));
            })
            ->values();
    }
}
