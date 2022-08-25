<?php

namespace ChatBundle\Repository;

use AppBundle\Entity\Client;
use AppBundle\Entity\Event;
use AppBundle\Enums\ChatMessageStatus;
use AppBundle\Repository\ClientStatusRepository;
use AppBundle\Repository\EventRepository;
use ChatBundle\Entity\Conversation;
use ChatBundle\Entity\Message as Entity;
use ChatBundle\Entity\Message;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Entity|null find($id, $lockMode = null, $lockVersion = null)
 * @method Entity|null findOneBy(array $criteria, array $orderBy = null)
 * @method Entity[]    findAll()
 * @method Entity[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MessageRepository extends ServiceEntityRepository
{
    /** @var class-string<Entity> */
    protected $_entityName = Entity::class;
    private ConversationRepository $conversationRepository;
    private ClientStatusRepository $clientStatusRepository;
    private EventRepository $eventRepository;

    public function __construct(ManagerRegistry $registry,
                                ConversationRepository $conversationRepository,
                                ClientStatusRepository $clientStatusRepository,
                                EventRepository $eventRepository
    ) {
        /** @var EntityManager $em */
        $em = $registry->getManagerForClass($this->_entityName);
        $this->_em = $em;

        $this->_class = $this->_em->getClassMetadata($this->_entityName);
        $this->conversationRepository = $conversationRepository;
        $this->clientStatusRepository = $clientStatusRepository;
        $this->eventRepository = $eventRepository;

        parent::__construct($registry, $this->_entityName);
    }

    /**
     * @param array<Client> $clients
     * @return array{ids: array, latestMessagesSentDates: array, oldestUnreadMessagesDates: array, unreadCounts: array, unansweredCounts: array}
     */
    public function getMessageStatsByMultipleClients(array $clients): array
    {
        $conversationsIds = $this->conversationRepository
            ->createQueryBuilder('c')
            ->select('c.id conversation_id, IDENTITY(c.client) client_id')
            ->andWhere('c.client IN (:clients)')
            ->setParameter('clients', $clients)
            ->getQuery()
            ->getResult();

        $unreadCounts = $this->conversationRepository
            ->createQueryBuilder('conv')
            ->select('COUNT(m.id) data, IDENTITY(m.client) client_id')
            ->leftJoin('conv.messages', 'm', 'WITH', 'm.client IN (:clients)')
            ->setParameter('clients', $clients)
            ->groupBy('m.client')
            ->andWhere('conv.client IN (:clients)')
            ->andWhere('m.client IS NOT NULL')
            ->andWhere('m.isNew = true')
            ->andWhere('m.deleted = false')
            ->getQuery()
            ->getResult();

        $unansweredCount = $this->clientStatusRepository
            ->createQueryBuilder('cs')
            ->select('count(cs.id) data, IDENTITY(cs.client) client_id')
            ->andWhere('cs.resolved = 0')
            ->andWhere('cs.event = :event')
            ->andWhere('cs.client IN (:clients)')
            ->groupBy('cs.client')
            ->setParameter('clients', $clients)
            ->setParameter(':event', $this->eventRepository->findOneByName(Event::SENT_MESSAGE))
            ->getQuery()
            ->getResult();

        $latestMessagesSentDates =  $this->createQueryBuilder('m')
            ->select('MAX(m.sentAt) data, IDENTITY(conv.client) client_id')
            ->innerJoin('m.conversation', 'conv')
            ->andWhere('conv.client IN (:clients)')
            ->setParameter('clients', $clients)
            ->groupBy('conv.client')
            ->getQuery()
            ->getResult();

        $oldestUnreadMessagesDates =  $this->createQueryBuilder('m')
            ->select('MAX(m.sentAt) data, IDENTITY(m.client) client_id')
            ->andWhere('m.isNew = 1')
            ->andWhere('m.client IN (:clients)')
            ->setParameter('clients', $clients)
            ->groupBy('m.client')
            ->getQuery()
            ->getResult();

        return [
            'ids' => $conversationsIds,
            'latestMessagesSentDates' => $latestMessagesSentDates,
            'oldestUnreadMessagesDates' => $oldestUnreadMessagesDates,
            'unreadCounts' => $unreadCounts,
            'unansweredCounts' => $unansweredCount
        ];
    }

    /** @return array{id: int, lastMessageSent: ?string, oldestUnreadMessage: ?string, unreadCount: int, unansweredCount: int} */
    public function getMessageStatsByClient(Client $client): array
    {
        $conversation = $this->conversationRepository->findByClient($client);
        $id = $conversation ? $conversation->getId() : 0;
        $unreadCount = $conversation ? $this->getUnreadMessagesCount($conversation, 'client') : 0;
        $unansweredCount = $conversation ? $this->getUnansweredMessagesCount($client) : 0;

        $lastMessageSent = null;
        $oldestUnreadMessageSent = null;
        if ($conversation) {
            $latestMessage = $this->getLatestMessage($conversation);
            if ($latestMessage !== null) {
                $lastMessageSent = $latestMessage->getSentAt()->format(\DateTime::ISO8601);
            }
            $oldestUnreadMessage = $this->getOldestUnreadMessage($conversation);
            if ($oldestUnreadMessage !== null) {
                $oldestUnreadMessageSent = $oldestUnreadMessage->getSentAt()->format(\DateTime::ISO8601);
            }
        }

        return [
            'id' => $id,
            'lastMessageSent' => $lastMessageSent,
            'oldestUnreadMessage' => $oldestUnreadMessageSent,
            'unreadCount' => $unreadCount,
            'unansweredCount' => $unansweredCount
        ];
    }

    public function getLatestMessage(Conversation $conversation): ?Message
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.conversation = :conversation')
            ->setParameter('conversation', $conversation)
            ->orderBy('m.sentAt', 'desc')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getOldestUnreadMessage(Conversation $conversation): ?Message
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.conversation = :conversation')
            ->andWhere('m.isNew = 1')
            ->andWhere('m.client is not null')
            ->setParameter('conversation', $conversation)
            ->orderBy('m.sentAt', 'desc')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getUnansweredMessagesCount(Client $client): int
    {
        return $this->clientStatusRepository
            ->createQueryBuilder('cs')
            ->select('count(cs.id)')
            ->andWhere('cs.resolved = 0')
            ->andWhere('cs.event = :event')
            ->andWhere('cs.client = :client')
            ->setParameter('client', $client)
            ->setParameter(':event', $this->eventRepository->findOneByName(Event::SENT_MESSAGE))
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getUnreadMessagesCount(Conversation $conversation, string $side = 'all'): int
    {
        $qb = $this
            ->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->andWhere('m.isNew = true')
            ->andWhere('m.deleted = false')
        ;

        if ($side === 'client') {
            $qb->andWhere('m.client IS NOT NULL');
        } elseif ($side === 'user') {
            $qb->andWhere('m.user IS NOT NULL');
        }
        $qb->andWhere('m.conversation = :conversation')
            ->setParameter('conversation', $conversation);

        return $qb->getQuery()->getSingleScalarResult();
    }

    public function markAsSeenByClient(Client $client, Conversation $conversation): void
    {
        $newMessagesIds = $this->createQueryBuilder('m')
            ->select('m.id')
            ->andWhere('m.client is null and m.user = :user and m.conversation = :conversation and m.isNew = 1')
            ->setParameter('conversation', $conversation)
            ->setParameter('user', $client->getUser())
            ->getQuery()
            ->getArrayResult();

        $newMessagesIds = array_map(static function (array $value) {
            return $value['id'];
        }, $newMessagesIds);

        if (count($newMessagesIds) > 0) {
            $this->createQueryBuilder('m')
                ->update()
                ->set('m.isNew', ':isNew')
                ->set('m.status', ':status')
                ->setParameter('status', ChatMessageStatus::READ)
                ->setParameter('isNew', false)
                ->andWhere('m.id in (:ids)')
                ->setParameter('ids', $newMessagesIds)
                ->getQuery()
                ->execute();
        }
    }

    /**
     * @param Client $client
     * @return mixed
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function markAsSeenByTrainer(Client $client)
    {
        return $this->createQueryBuilder('m')
            ->update()
            ->set('m.isNew', ':isNew')
            ->where('m.user = :user')
            ->andWhere('m.client = :client')
            ->setParameters([
                'user' => $client->getUser(),
                'client' => $client,
                'isNew' => false,
            ])
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function markLastAsUnreadByTrainer(Conversation $conversation): ?Message
    {
        $message = $this->findOneBy(
            [
                'client' => $conversation->getClient(),
                'conversation' => $conversation,
                'isNew' => false,
            ],
            [
                'id' => 'DESC'
            ]
        );

        if ($message) {
            $message->setIsNew(true);
            $this->getEntityManager()->flush();
        }

        return $message;
    }

    public function persist(object $entity): void
    {
        $this->_em->persist($entity);
    }

    public function flush(): void
    {
        $this->_em->flush();
    }
}
