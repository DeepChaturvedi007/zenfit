<?php declare(strict_types=1);

namespace AppBundle\Repository;

use AppBundle\Entity\Lead;
use AppBundle\Entity\User;
use AppBundle\Transformer\LeadTransformer;
use AppBundle\Entity\Lead as Entity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;
use GymBundle\Entity\Gym;

/**
 * @method Entity|null find($id, $lockMode = null, $lockVersion = null)
 * @method Entity|null findOneBy(array $criteria, array $orderBy = null)
 * @extends ServiceEntityRepository<Entity>
 */
class LeadRepository extends ServiceEntityRepository
{
    /** @var class-string<Entity> */
    protected $_entityName = Entity::class;
    private LeadTransformer $leadTransformer;

    public function __construct(ManagerRegistry $registry, LeadTransformer $leadTransformer)
    {
        /** @var EntityManager $em */
        $em = $registry->getManagerForClass($this->_entityName);
        $this->_em = $em;

        $this->_class = $this->_em->getClassMetadata($this->_entityName);
        $this->leadTransformer = $leadTransformer;

        parent::__construct($registry, $this->_entityName);
    }

    public function getLeadsByGym(Gym $gym, int $offset, int $limit, ?string $query, bool $count, \DateTime $start = null, \DateTime $end = null) : array
    {
        $qb = $this->createQueryBuilder('l');
        if ($count) {
            $qb->select('count(l.id) as count');
        }

        $qb
            ->leftJoin('l.client', 'c')
            ->leftJoin('l.payment', 'p')
            ->leftJoin('c.user', 'u')
            ->leftJoin('l.user', 'ul')
            ->andWhere('l.user in (:users)')
            ->andWhere('l.deleted = 0')
            ->setParameter('users', $gym->getUsers());

        if (!$count && $limit != -1) {
            $qb
                ->setFirstResult($offset)
                ->setMaxResults($limit)
                ->orderBy('l.id', 'DESC');
        }

        if ($query !== null) {
            $qb
                ->andWhere('l.name like :query or l.email like :query or l.phone like :query')
                ->setParameter('query', '%' . $query . '%');
        }

        if ($start !== null && $end !== null) {
            $qb
                ->andWhere('l.createdAt BETWEEN :start AND :end')
                ->setParameter('start', $start->format('Y-m-d'))
                ->setParameter('end', $end->format('Y-m-d 23:59:59'));
        }

        if (!$count) {
            return collect($qb->getQuery()->getResult())
                ->map(function(Lead $lead) {
                    return $this->leadTransformer->transform($lead);
                })->toArray();
        }

        return $qb->getQuery()->getResult();
    }

    public function findAllLeadsByUser(User $user, ?string $q, ?string $hasTag = null, $status = null, ?int $limit = 30, ?int $offset = 0, bool $asCount = false, \DateTime $start = null, \DateTime $end = null): array
    {
        $qb = $this->createQueryBuilder('l')
            ->where('l.user = :user')
            ->andWhere('l.deleted = 0')
            ->setParameter('user', $user);

        if ($hasTag !== null && $hasTag != '') {
            $qb->innerJoin('l.tags', 'tags')
                ->andWhere('tags.title = :tag')
                ->setParameter('tag', $hasTag);
        }

        if (!empty($q)) {
            $qb
                ->andWhere('l.name like :query or l.email like :query or l.phone like :query')
                ->setParameter('query', '%' . $q . '%');
        }

        if ($start !== null && $end !== null) {
            $qb
                ->andWhere('l.createdAt BETWEEN :start AND :end')
                ->setParameter('start', $start->format('Y-m-d'))
                ->setParameter('end', $end->format('Y-m-d 23:59:59'));
        }

        if ($asCount) {
            $qb->select('l.status, count(l.id) as count')
                ->groupBy('l.status');

            $leads = $new = $inDialog = $won = $lost = $paymentWaiting = $noAnswer = 0;
            foreach ($qb->getQuery()->getResult() as $row) {
                if (!array_key_exists('status', $row) || !array_key_exists('count', $row)) {
                    throw new \RuntimeException();
                }

                $leads += $row['count'];

                if ($row['status'] === Lead::LEAD_NEW) {
                    $new = $row['count'];
                } elseif ($row['status'] === Lead::LEAD_IN_DIALOG) {
                    $inDialog = $row['count'];
                } elseif ($row['status'] === Lead::LEAD_WON) {
                    $won = $row['count'];
                } elseif ($row['status'] === Lead::LEAD_PAYMENT_WAITING) {
                    $paymentWaiting = $row['count'];
                } elseif ($row['status'] === Lead::LEAD_NO_ANSWER) {
                    $noAnswer = $row['count'];
                } else {
                    $lost = $row['count'];
                }
            }
        } else {
            if ($status !== null && $status != 0) {
                $qb->andWhere('l.status = :status')
                    ->setParameter('status', $status);
            }

            $qb->addOrderBy('l.createdAt', 'DESC');

            $qb->setFirstResult($offset);
            $qb->setMaxResults($limit);

            $leads = collect($qb->getQuery()->getResult())
                ->map(function(Lead $lead) {
                    return $this->leadTransformer->transform($lead);
                })->toArray();

            $new = $inDialog = $won = $lost = $paymentWaiting = $noAnswer = [];
            foreach ($leads as $lead) {
                if ($lead['status'] === Lead::LEAD_NEW) {
                    $new[] = $lead;
                } elseif ($lead['status'] === Lead::LEAD_IN_DIALOG) {
                    $inDialog[] = $lead;
                } elseif ($lead['status'] === Lead::LEAD_WON) {
                    $won[] = $lead;
                } elseif ($lead['status'] === Lead::LEAD_PAYMENT_WAITING) {
                    $paymentWaiting[] = $lead;
                } elseif ($lead['status'] === Lead::LEAD_NO_ANSWER) {
                    $noAnswer[] = $lead;
                } else {
                    $lost[] = $lead;
                }
            }
        }

        return [
            'all' => $leads,
            'new' => $new,
            'inDialog' => $inDialog,
            'won' => $won,
            'lost' => $lost,
            'noAnswer' => $noAnswer,
            'paymentWaiting' => $paymentWaiting
        ];
    }

    public function getByUser(User $user)
    {
        return $this->findBy([
            'user' => $user,
            'deleted' => 0
        ]);
    }

}
