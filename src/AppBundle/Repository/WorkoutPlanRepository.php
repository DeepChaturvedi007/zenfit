<?php

namespace AppBundle\Repository;
use AppBundle\Entity\User;
use AppBundle\Entity\WorkoutPlan;
use AppBundle\Entity\Workout;
use AppBundle\Entity\WorkoutDay;
use AppBundle\Entity\Client;
use AppBundle\Entity\WorkoutPlan as Entity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use WorkoutPlanBundle\Transformer\WorkoutPlanTransformer;

/**
 * @method Entity|null find($id, $lockMode = null, $lockVersion = null)
 * @method Entity|null findOneBy(array $criteria, array $orderBy = null)
 * @extends ServiceEntityRepository<Entity>
 */
class WorkoutPlanRepository extends ServiceEntityRepository
{
    /** @var class-string<Entity> */
    protected $_entityName = Entity::class;
    private WorkoutPlanTransformer $workoutPlanTransformer;

    public function __construct(ManagerRegistry $registry, WorkoutPlanTransformer $workoutPlanTransformer)
    {
        /** @var EntityManager $em */
        $em = $registry->getManagerForClass($this->_entityName);
        $this->_em = $em;

        $this->_class = $this->_em->getClassMetadata($this->_entityName);
        $this->workoutPlanTransformer = $workoutPlanTransformer;

        parent::__construct($registry, $this->_entityName);
    }


    public function get(int $id): Entity
    {
        $object = $this->find($id);
        if ($object === null) {
            throw new NotFoundHttpException(Entity::class .' not found');
        }

        return $object;
    }

    /**
     * @param int $id
     * @param User $user
     * @return WorkoutPlan
     */
    public function getByIdAndUser($id, User $user)
    {
        $qb = $this->createQueryBuilder('wp');

        return $qb
            ->where('wp.id = :id')
            ->andWhere('wp.user = :user')
            ->setParameters([
                'id' => $id,
                'user' => $user,
            ])
            ->getQuery()
            ->getSingleResult();
    }

    /**
     * @param int[] $ids
     * @param User $user
     * @param bool $template
     * @return WorkoutPlan[]
     */
    public function getByIdsAndUser($ids, User $user, $template = false)
    {
        if ($user->isAssistant()) {
            $user = $user->getGymAdmin();
        }

        $qb = $this->createQueryBuilder('wp');

        return $qb
            ->where('wp.user = :user')
            ->andWhere($qb->expr()->in('wp.id', $ids))
            ->andWhere('wp.template = :template')
            ->setParameters([
                'user' => $user,
                'template' => $template,
            ])
            ->getQuery()
            ->getResult();
    }

    /** @return array<mixed> */
    public function getAllByUser(User $user, bool $template = false, ?int $location = null, ?int $workoutsPerWeek = null, ?int $gender = null, ?int $level = null): array
    {
        $qb = $this->createQueryBuilder('wp');

        if ($location !== null || $workoutsPerWeek !== null || $gender !== null || $level !== null) {
            $qb->innerJoin('wp.workoutPlanMeta', 'm');
        }

        if ($location !== null) {
            $qb->andWhere('m.location = :location')
                ->setParameter('location', $location);
        }
        if ($workoutsPerWeek !== null) {
            $qb->andWhere('m.workoutsPerWeek = :workoutsPerWeek')
                ->setParameter('workoutsPerWeek', $workoutsPerWeek);
        }
        if ($gender !== null) {
            $qb->andWhere('m.gender = :gender')
                ->setParameter('gender', $gender);
        }
        if ($level !== null) {
            $qb->andWhere('m.level = :level')
                ->setParameter('level', $level);
        }

        $qb
            ->andWhere('wp.user = :user')
            ->andWhere('wp.template = :template')
            ->andWhere('wp.deleted = 0')
            ->setParameter('user', $user)
            ->setParameter('template', $template)
            ->orderBy('wp.name', 'ASC');

        return collect($qb->getQuery()->getResult())
            ->map(function(WorkoutPlan $plan) {
                return $this->workoutPlanTransformer->transform($plan);
            })->toArray();
    }

    /** @return array<Entity> */
    public function getAllByClientAndUser(Client $client, User $user): array
    {
        if ($user->isAssistant()) {
            $user = $user->getGymAdmin();
        }

        $qb = $this->createQueryBuilder('wp');

        $query = $qb
            ->where('wp.user = :user')
            ->andWhere('wp.client = :client')
            ->andWhere('wp.template = :template')
            ->andWhere('wp.deleted = 0')
            ->setParameters([
                'user' => $user,
                'client' => $client,
                'template' => false,
            ])
            ->orderBy('wp.id', 'DESC');

        return collect($query->getQuery()->getResult())
            ->map(function(WorkoutPlan $plan) {
                return $this->workoutPlanTransformer->transform($plan);
            })->toArray();
    }

    /**
     * @param array<string> $tags
     * @return array<WorkoutPlan>
     */
    public function getPlansToAutoAssignByUser(User $user, array $tags): array
    {
        // Extend tags array by #all tag
        array_push($tags, '#all');
        /** @var QueryBuilder $qb */
        $qb = $this->createQueryBuilder('wp');

        $orStatements = $qb->expr()->orX();
        foreach ($tags as $tag) {
            $orStatements->add(
                $qb->expr()->like('wp.assignmentTags', $qb->expr()->literal('%"' . $tag . '"%'))
            );
        }

        /** @var WorkoutPlan[] $result */
        $result = $qb->join('wp.user','u')
            ->where('wp.user = :user')
            ->andWhere($orStatements)
            ->andWhere('wp.assignmentTags IS NOT NULL')
            ->andWhere('wp.deleted = 0')
            ->andWhere('wp.template = 1')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();

        return $result;
    }

    public function getPlanByClient(
        Client $client,
        array $status = [WorkoutPlan::STATUS_ACTIVE, WorkoutPlan::STATUS_HIDDEN, WorkoutPlan::STATUS_INACTIVE],
        $current = false
    ) {
        $qb = $this->createQueryBuilder('wp');

        $qb
            ->where('wp.client = :client')
            ->andWhere($qb->expr()->in('wp.status', $status))
            ->andWhere('wp.user IS NOT null')
            ->andWhere('wp.deleted = 0')
            ->setParameter('client', $client)
            ->orderBy('wp.createdAt', 'DESC');

        //apply filter in order to find latest clients plan
        if ($current) {
            return $qb
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult();
        }

        return $qb
            ->getQuery()
            ->getResult();
    }

    /**
     * @param array<Client> $clients
     * @return array<mixed>
     */
    public function getCountByClients(array $clients): array
    {
        return $this->createQueryBuilder('wp')
            ->select('count(wp.id) as count, IDENTITY(wp.client) as client_id')
            ->andWhere('wp.client in (:clients)')
            ->andWhere('wp.deleted = 0')
            ->setParameter('clients', $clients)
            ->groupBy('wp.client')
            ->getQuery()
            ->getArrayResult();
    }

    public function persist(Entity $entity): void
    {
        $this->_em->persist($entity);
    }

    public function flush(): void
    {
        $this->_em->flush();
    }
}
