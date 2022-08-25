<?php

namespace AppBundle\Repository;

use AppBundle\Entity\MasterMealPlan;
use AppBundle\Entity\User;
use AppBundle\Entity\Client;
use AppBundle\Entity\MasterMealPlan as Entity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @method Entity|null find($id, $lockMode = null, $lockVersion = null)
 * @method Entity|null findOneBy(array $criteria, array $orderBy = null)
 * @extends ServiceEntityRepository<Entity>
 */
class MasterMealPlanRepository extends ServiceEntityRepository
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

    public function get(int $id): Entity
    {
        $object = $this->find($id);
        if ($object === null) {
            throw new NotFoundHttpException(Entity::class .' not found');
        }

        return $object;
    }

    /**
     * @param User $user
     * @param bool $template
     * @return MasterMealPlan[]
     */
    public function getAllByUser(User $user, $template = false)
    {
        $qb = $this->createQueryBuilder('mmp');

        return $qb
            ->where('mmp.user = :user')
            ->andWhere('mmp.template = :template')
            ->andWhere('mmp.deleted = 0')
            ->setParameters([
                'user' => $user,
                'template' => $template,
            ])
            ->orderBy('mmp.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param array<string> $select
     * @return array<MasterMealPlan>|array<mixed>
     */
    public function getAllByClientAndUser(Client $client, User $user, array $select = []): array
    {
        $qb = $this
            ->createQueryBuilder('mmp')
            ->where('mmp.user = :user')
            ->andWhere('mmp.client = :client')
            ->andWhere('mmp.template = :template')
            ->andWhere('mmp.deleted = 0')
            ->setParameters([
                'user' => $user,
                'client' => $client,
                'template' => false,
            ])
            ->orderBy('mmp.id', 'DESC');

        foreach ($select as $field) {
            $qb->addSelect('mmp.' . $field);
        }

        return $qb->getQuery()
            ->getResult();
    }

    /**
     * @param User $user
     * @param array $tags
     * @return mixed
     */
    public function getPlansToAutoAssignByUser(User $user, array $tags)
    {
        // Extend tags array by #all tag
        array_push($tags, '#all');
        /** @var QueryBuilder $qb */
        $qb = $this->createQueryBuilder('mmp');

        $orStatements = $qb->expr()->orX();
        foreach ($tags as $tag) {
            $orStatements->add(
                $qb->expr()->like('mmp.assignmentTags', $qb->expr()->literal('%"' . $tag . '"%'))
            );
        }

        return $qb->join('mmp.user','u')
            ->where('mmp.user = :user')
            ->andWhere($orStatements)
            ->andWhere('mmp.assignmentTags IS NOT NULL')
            ->andWhere('mmp.deleted = 0')
            ->andWhere('mmp.template = 1')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param int $id
     * @param User $user
     * @return MasterMealPlan
     */
    public function getByIdAndUser($id, User $user)
    {
        $qb = $this->createQueryBuilder('mmp');

        return $qb
            ->where('mmp.id = :id')
            ->andWhere('mmp.user = :user')
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
     * @return MasterMealPlan[]
     */
    public function getByIdsAndUser($ids, User $user, $template = false)
    {
        $qb = $this->createQueryBuilder('mmp');

        return $qb
            ->where('mmp.user = :user')
            ->andWhere($qb->expr()->in('mmp.id', $ids))
            ->andWhere('mmp.template = :template')
            ->setParameters([
                'user' => $user,
                'template' => $template,
            ])
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Client $client
     * @param array<mixed> $status
     * @param bool $includeDeleted
     * @return array<mixed>
     */
    public function getByClient(Client $client, array $status = [], $includeDeleted = false): array
    {
        $qb = $this->createQueryBuilder('mmp');
        $qb
            ->where('mmp.client = :client')
            ->andWhere('mmp.template = 0');

        if (!$includeDeleted) {
            $qb->andWhere('mmp.deleted = 0');
        }

        if (count($status) > 0) {
            $qb->andWhere($qb->expr()->in('mmp.status', $status));
        }

        return $qb
            ->setParameters([
                'client' => $client,
            ])
            ->orderBy('mmp.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function getSubscribedMasterMealPlanByClient(Client $client): ?MasterMealPlan
    {
        return $this->createQueryBuilder('mmp')
            ->where('mmp.client = :client')
            ->andWhere('mmp.started IS NOT NULL')
            ->setParameter('client', $client)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param array<Client> $clients
     * @return array<mixed>
     */
    public function getCountByClients(array $clients): array
    {
        return $this->createQueryBuilder('mmp')
            ->select('count(mmp.id) as count, IDENTITY(mmp.client) as client_id')
            ->andWhere('mmp.client in (:clients)')
            ->andWhere('mmp.deleted = 0')
            ->setParameter('clients', $clients)
            ->groupBy('mmp.client')
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * @param array<Client> $clients
     * @return array<mixed>
     */
    public function getPreviousKcalsStats(array $clients): array
    {
        $data =  $this->createQueryBuilder('mmp')
            ->select('max(mmp.id) mmp_id')
            ->andWhere('mmp.client in (:clients)')
            ->andWhere('mmp.deleted = 0')
            ->setParameter('clients', $clients)
            ->groupBy('mmp.client')
            ->getQuery()
            ->getArrayResult();

        $mmpIds = array_map(static fn ($item) => $item['mmp_id'], $data);

        $result = $this->createQueryBuilder('mmp')
            ->select('mmp.desiredKcals as kcals, IDENTITY(mmp.client) client_id')
            ->andWhere('mmp.id in (:ids)')
            ->setParameter('ids', $mmpIds)
            ->getQuery()
            ->getResult();

        return $result;
    }

    /**
     * @param Client $client
     * @return array<mixed>
     */
    public function getPreviousMealPlanKcalsByClient(Client $client): array
    {
        return collect($this->getByClient($client))
            ->flatMap(function(MasterMealPlan $plan) {
                $date = $plan->getCreatedAt()->format('Y-m-d');
                return [$date => $plan->getDesiredKcals()];
            })
            ->reverse()
            ->toArray();
    }
}
