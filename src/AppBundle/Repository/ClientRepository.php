<?php declare(strict_types=1);

namespace AppBundle\Repository;

use AppBundle\Entity\Client;
use AppBundle\Entity\ClientTag;
use AppBundle\Entity\Queue;
use AppBundle\Entity\User;
use AppBundle\Entity\Event;
use Carbon\Carbon;
use ChatBundle\Repository\MessageRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\Persistence\ManagerRegistry;
use GymBundle\Entity\Gym;
use ClientBundle\Transformer\ClientTransformer;

/**
 * @method Client|null find($id, $lockMode = null, $lockVersion = null)
 * @method Client|null findOneBy(array $criteria, array $orderBy = null)
 * @extends ServiceEntityRepository<Client>
 */
class ClientRepository extends ServiceEntityRepository
{
    /** @var class-string<Client> */
    protected $_entityName = Client::class;
    private ClientTransformer $clientTransformer;
    private EventRepository $eventRepository;
    private MessageRepository $messageRepository;
    private DocumentClientRepository $documentClientRepository;
    private VideoClientRepository $videoClientRepository;
    private WorkoutPlanRepository $workoutPlanRepository;
    private MasterMealPlanRepository $masterMealPlanRepository;

    private const CACHE_COUNT_SECONDS = 600;

    public function __construct(
        ManagerRegistry $registry,
        EventRepository $eventRepository,
        MessageRepository $messageRepository,
        DocumentClientRepository $documentClientRepository,
        VideoClientRepository $videoClientRepository,
        MasterMealPlanRepository $masterMealPlanRepository,
        WorkoutPlanRepository $workoutPlanRepository,
        ClientTransformer $clientTransformer
    ){
        /** @var EntityManager $em */
        $em = $registry->getManagerForClass($this->_entityName);
        $this->_em = $em;
        $this->clientTransformer = $clientTransformer;
        $this->eventRepository = $eventRepository;
        $this->documentClientRepository = $documentClientRepository;
        $this->messageRepository = $messageRepository;
        $this->videoClientRepository = $videoClientRepository;
        $this->workoutPlanRepository = $workoutPlanRepository;
        $this->masterMealPlanRepository = $masterMealPlanRepository;

        $this->_class = $this->_em->getClassMetadata($this->_entityName);

        parent::__construct($registry, $this->_entityName);
    }

    public function endingSoon(Client $client)
    {
        $startDate = $client->getStartDate();
        $duration = $client->getDuration();
        if ($startDate !== null && $duration > 0 && $duration < 13) {
            $finalDate = Carbon::instance($startDate)->addMonths($duration);
            $days = Carbon::today()->diffInDays($finalDate, false);

            if ($days > 0 && $days <= 7) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<Client> $clients
     * @return array{messages: array, videos: array, documents: array, workout_plans: array}
     */
    public function getStatsByClients(array $clients): array
    {
        return [
            'previous_kcals' => $this->masterMealPlanRepository->getPreviousKcalsStats($clients),
            'messages' => $this->messageRepository->getMessageStatsByMultipleClients($clients),
            'videos' => $this->videoClientRepository->getCountByClients($clients),
            'documents' => $this->documentClientRepository->getCountByClients($clients),
            'workout_plans' => $this->workoutPlanRepository->getCountByCLients($clients),
            'master_meal_plans' => $this->masterMealPlanRepository->getCountByCLients($clients),
        ];
    }

    /**
     * @param array $ids
     *
     * @return \Illuminate\Support\Collection
     * @throws \Exception
     */
    public function getInvitationByIds(array $ids)
    {
        if (empty($ids)) {
            return collect();
        }

        $qb = $this->createQueryBuilder('c');

        $now = new \DateTime();
        $now->modify('-1 hour');

        $result = $qb
            ->select(['c.id AS clientId', 'e.status as status', 'e.createdAt'])
            ->leftJoin('c.emails', 'e', Join::WITH, 'e.client = c.id')
            ->where($qb->expr()->in('c.id', $ids))
            ->andWhere($qb->expr()->eq('e.type', Queue::TYPE_CLIENT_EMAIL))
            ->groupBy('e.client', 'e.status', 'e.createdAt')
            ->getQuery()
            ->getArrayResult();

        return collect($result)
            ->keyBy(function ($item) {
                return $item['clientId'];
            })
            ->map(function ($item) use ($now) {
                $created = $item['createdAt'] ?: clone $now;
                $item['delay'] = $created->getTimestamp() <= $now->getTimestamp() ? null : $created->diff($now)->format('%i');

                return $item;
            });
    }

    public function getClientsByFilters(
        User $user,
        $active,
        $q,
        $offset = null,
        $limit = null,
        array $filters = [],
        array $tags = [],
        bool $asCount = false,
        ?string $sortColumn = null,
        string $sortOrder = 'ASC',
        int $daysSinceActivated = 0
    )
    {
        $filters = collect($filters);

        $parameters = [
            'user' => $user,
            'active' => $active,
            'deleted' => false,
        ];

        $where = [
            'c.user = :user',
            'c.active = :active',
            'c.deleted = :deleted'
        ];

        $select = ['c.id'];

        $select = array_merge($select, ['IDENTITY(cs.event) AS event_id', 'cs.resolved']);

        $qb = $this->createQueryBuilder('c');
        $qb->select($select);

        $clientRemindersUnresolved = $this->eventRepository->findOneByName(Event::CLIENT_REMINDERS_UNRESOLVED);
        if ($clientRemindersUnresolved === null) {
            throw new \RuntimeException('CLIENT_REMINDERS_UNRESOLVED event is not in DB');
        }
        if (!$filters->containsStrict($clientRemindersUnresolved) && $filters->isNotEmpty()) {
            $parameters['resolved'] = false;
            $where[] = 'cs.resolved = :resolved';
        }

        if ($filters->containsStrict($clientRemindersUnresolved)) {
            $qb->innerJoin('c.reminders', 'reminders');
                $where[] = 'reminders.deleted = 0 and reminders.resolved = 0';
        }


        $needWelcomeEvent = $this->eventRepository->findOneByName(Event::NEED_WELCOME);
        if ($needWelcomeEvent === null) {
            throw new \RuntimeException('NEED_WELCOME event not found in DB');
        }

        $qb->leftJoin('c.clientStatus', 'cs');

        if ($daysSinceActivated > 0) {
            $where[] = $qb->expr()->andX(
                $qb->expr()->eq('cs.event', ':needsWelcomeEvent'),
                $qb->expr()->lte('cs.resolvedBy', ':resolvedBy'),
                $qb->expr()->eq('cs.resolved', true)
            );
            $parameters['needsWelcomeEvent'] = $needWelcomeEvent;
            $parameters['resolvedBy'] = new \DateTime("-$daysSinceActivated days");
        }

        if ($q) {
            $search = $qb->expr()->literal('%' . $q . '%');
            $where[] = $qb->expr()->like('c.name', $search);
        }

        if (count($tags) > 0) {
            $qb->leftJoin('c.tags', 'ct', Join::WITH, 'ct.client = c.id');
            $where[] = $qb->expr()->in('ct.title', $tags);
        }

        foreach ($where as $condition) {
            $qb->andWhere($condition);
        }

        $qb->setParameters($parameters);
        $query = $qb->getQuery();

        /*if ($asCount) {
            $query = $query->enableResultCache(self::CACHE_COUNT_SECONDS);
        }*/

        $result = collect($query->getArrayResult());
        $clients = $result->groupBy('id');

        //only check for Event::NEED_WELCOME event
        //for clients that are active, eg. have active = true
        //clients that are inactive should all be listed in the same table
        //regardless if they have resolved Event::NEED_WELCOME or not.
        if ($active) {
            $clients = $clients->filter(function (\Illuminate\Support\Collection $events) use ($filters, $needWelcomeEvent, $clientRemindersUnresolved) {
                $clientNeedsWelcome = $events->contains(function (array $client) use ($needWelcomeEvent) {
                    return (int) $client['event_id'] === $needWelcomeEvent->getId() && $client['resolved'] === false;
                });

                if ($filters->containsStrict($needWelcomeEvent)) {
                    return $clientNeedsWelcome;
                }

                if ($clientNeedsWelcome) {
                    return false;
                }

                if (!$filters->containsStrict($clientRemindersUnresolved) && $filters->isNotEmpty()) {
                    return $events->contains(function (array $client) use ($filters) {
                        /** @var Event $filter */
                        foreach ($filters as $filter) {
                            if ($filter->getId() === (int) $client['event_id']) {
                                return true;
                            }
                        }

                        return false;
                    });
                }

                return true;
            });
        }


        if ($asCount) {
            return $clients->count();
        }

        if ($clients->isNotEmpty()) {
            $qb = $this
                ->createQueryBuilder('c')
                ->where($qb->expr()->in('c.id', $clients->keys()->all()))
            ;

            $sortOrder = (in_array($sortOrder, ['ASC', 'DESC'])) ? $sortOrder : 'ASC';
            switch ($sortColumn) {
                case 'name':
                    $qb->addOrderBy('c.demoClient','DESC');
                    $qb->addOrderBy('c.name', $sortOrder);
                    break;

                case 'status':
                    $qb
                        ->addSelect('COALESCE(COUNT(DISTINCT(clientStatus)), 0) as HIDDEN orderColumn')
                        ->leftJoin('c.clientStatus', 'clientStatus', 'WITH', 'clientStatus.resolved = false')
                        ->addGroupBy('c.id')
                        ->addOrderBy('orderColumn', $sortOrder)
                    ;
                    break;

                case 'weeks':
                    $qb
                        ->addSelect('COALESCE(c.startDate, c.createdAt) as HIDDEN orderColumn')
                        ->addGroupBy('c.id')
                        ->addOrderBy('orderColumn', $sortOrder)
                    ;
                    break;

                case 'checkin_day':
                    $qb->addOrderBy('c.bodyProgressUpdated', $sortOrder);
                    break;

                case 'messages':
                    $qb
                        ->addSelect('MAX(messages.sentAt) as HIDDEN orderColumn')
                        ->addSelect("CASE WHEN MAX(messages.sentAt) is null THEN 0 ELSE 1 END HIDDEN _noUnreadMessages")
                        ->leftJoin('c.conversations', 'conversation', 'WITH', 'conversation.user = :conv_user')
                        ->setParameter('conv_user', $user)
                        ->leftJoin(
                            'conversation.messages',
                            'messages',
                            'WITH',
                            'messages.isNew = true AND messages.deleted = false AND messages.client IS NOT NULL'
                        )
                        ->addGroupBy('c.id')
                        ->addOrderBy('_noUnreadMessages', 'desc')
                        ->addOrderBy('orderColumn', $sortOrder)
                    ;
                    break;

                default:
                    $qb->addOrderBy('c.demoClient','DESC');
                    $qb->addOrderBy('c.name','ASC');
                    break;
            }

            if ($limit) {
                $qb
                    ->setMaxResults((int)$limit)
                    ->setFirstResult((int)$offset);
            }

            return $qb->getQuery()->getResult();
        }

        return [];
    }

    public function getClientsByGym(Gym $gym, int $offset, int $limit, ?string $query, bool $count) : array
    {
        $qb = $this->createQueryBuilder('c');
        if ($count) {
            $qb->select('count(c.id) as count');
        }

        $qb->andWhere('c.user in (:users)')
            ->andWhere('c.deleted = 0 and c.active = 1')
            ->leftJoin('c.user', 'u')
            ->setParameter('users', $gym->getUsers());

        if (!$count) {
            $qb->setFirstResult($offset)
                ->setMaxResults($limit)
                ->orderBy('c.id', 'DESC');
        }

        if ($query !== null) {
            $qb
                ->andWhere('c.name like :query or c.email like :query or c.phone like :query')
                ->setParameter('query', '%' . $query . '%');
        }

        $data = $qb->getQuery()->getResult();

        if (!$count) {
            return collect($data)->map(function(Client $client) {
                return $this->clientTransformer->transform($client);
            })->toArray();
        }

        return $data;
    }

    /**
     * @param array<string> $tags
     * @return array<Client>
     */
    public function getClientsByUser(User $user, string $keyword, array $tags, \DateTime $start = null, \DateTime $end = null): array
    {
        $qb = $this
            ->createQueryBuilder('c')
            ->leftJoin('c.tags', 'ct', Join::WITH, 'ct.client = c.id')
            ->where('c.user = :user')
            ->andWhere('c.deleted = 0')
            ->andWhere('c.active = 1')
            ->setParameter('user', $user);

        if ($keyword != '') {
            $qb
                ->andWhere('c.name LIKE :name')
                ->setParameter('name', '%' . $keyword . '%');
        }

        if ($start !== null && $end !== null) {
            $qb
                ->andWhere('c.createdAt BETWEEN :start AND :end')
                ->setParameter('start', $start->format('Y-m-d'))
                ->setParameter('end', $end->format('Y-m-d 23:59:59'));
        }

        $tags = collect($tags)
            ->map(function ($tag) {
                return trim($tag);
            })
            ->filter(function ($tag) {
                return !empty($tag);
            });

        if ($tags->isNotEmpty()) {
            $qb->andWhere($qb->expr()->in('ct.title', $tags->toArray()));
        }

        $qb->orderBy('c.name');

        return collect($qb->getQuery()->getResult())
            ->map(function (Client $client) {
                $tags = collect($client->getTags())
                    ->map(function (ClientTag $clientTag) {
                        return $clientTag->getTitle();
                    });

                return [
                    'id' => $client->getId(),
                    'name' => $client->getName(),
                    'photo' => $client->getPhoto(),
                    'active' => $client->getActive(),
                    'email' => $client->getEmail(),
                    'tags' => $tags->toArray(),
                ];
            })->toArray();
    }

    public function getByUser(User $user)
    {
        return $this->findBy([
            'user' => $user,
            'deleted' => 0,
            'active' => 1
        ]);
    }

    public function getClientsThatHaveAGoal(User $user)
    {
        return $this
            ->createQueryBuilder('c')
            ->where('c.user = :user')
            ->andWhere('c.goalWeight IS NOT NULL')
            ->andWhere('c.demoClient IS NULL')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return ResultSetMapping
     */
    private function getRSM()
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('amount', 'amount');

        return $rsm;
    }

    /**
     * @return ResultSetMapping
     */
    private function getClientsRSM()
    {
        $rsm = new ResultSetMapping();

        $rsm->addScalarResult('id', 'id');
        $rsm->addScalarResult('name', 'name');
        $rsm->addScalarResult('email', 'email');
        $rsm->addScalarResult('photo', 'photo');
        $rsm->addScalarResult('phone', 'phone');
        $rsm->addScalarResult('active', 'active');
        $rsm->addScalarResult('password', 'password');
        $rsm->addScalarResult('createdAt', 'createdAt');
        $rsm->addScalarResult('demoClient', 'demoClient');
        $rsm->addScalarResult('mealUpdated', 'mealUpdated');
        $rsm->addScalarResult('trainerViewed', 'trainerViewed');
        $rsm->addScalarResult('workoutUpdated', 'workoutUpdated');
        $rsm->addScalarResult('bodyProgressUpdated', 'bodyProgressUpdated');
        $rsm->addScalarResult('isActive', 'isActive');

        return $rsm;
    }

    public function persist(Client $entity): void
    {
        $this->_em->persist($entity);
    }

    public function flush(): void
    {
        $this->_em->flush();
    }
}
