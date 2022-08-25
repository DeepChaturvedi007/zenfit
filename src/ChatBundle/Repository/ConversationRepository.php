<?php

namespace ChatBundle\Repository;

use AppBundle\Entity\ClientTag;
use ChatBundle\Entity\Conversation as Entity;
use AppBundle\Entity\User;
use ChatBundle\Entity\Conversation;
use AppBundle\Entity\Client;
use ChatBundle\Entity\Message;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Driver\PDO\Statement;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Entity|null find($id, $lockMode = null, $lockVersion = null)
 * @method Entity|null findOneBy(array $criteria, array $orderBy = null)
 * @method Entity[]    findAll()
 * @method Entity[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ConversationRepository extends ServiceEntityRepository
{
    /** @var class-string<Entity> */
    protected $_entityName = Entity::class;

    public function __construct(
        ManagerRegistry $registry
    ) {
        /** @var EntityManager $em */
        $em = $registry->getManagerForClass($this->_entityName);
        $this->_em = $em;

        $this->_class = $this->_em->getClassMetadata($this->_entityName);

        parent::__construct($registry, $this->_entityName);
    }

    /**
     * @param array<mixed> $tags
     * @return array<mixed>
     */
    public function findByUser(User $user, ?string $q, array $tags = [], ?int $limit = null, ?int $offset = null): array
    {
        $subSelectQb = $this->_em->getConnection()
            ->createQueryBuilder()
            ->select('conversation.id id, conversation.user_id userId, conversation.client_id clientId, client.name client, client.photo clientImg, max(message.id) as latest_message_id')
            ->from('conversations', 'conversation')
            ->innerJoin('conversation', 'clients', 'client', 'conversation.client_id=client.id')
            ->leftJoin('conversation', 'messages', 'message', 'conversation.id=message.conversation_id')
            ->andWhere('conversation.user_id = '.$user->getId())
            ->andWhere('conversation.deleted = 0 and client.active = 1 and client.deleted = 0')
            ->addGroupBy('conversation.id')
            ->addOrderBy('latest_message_id', 'desc');

        if ($q != '') {
            $word = $subSelectQb->expr()->literal('%' . $q . '%');
            $subSelectQb->andWhere($subSelectQb->expr()->like('client.name', $word));
        }

        $subSelectQb->leftJoin('client', 'client_tags', 'ct','ct.client_id = client.id');

        if (!empty($tags)) {
            foreach ($tags as $tag) {
                $subSelectQb
                    ->andWhere('ct.title = '.$this->_em->getConnection()->quote($tag))
                    ->setParameter('tag', $tag);
            }
        }

        $qb = $this->_em->getConnection()
            ->createQueryBuilder()
            ->select('m.sent_at sentAt, m.content message, conv.*')
            ->from('messages', 'm')
            ->rightJoin('m', sprintf('(%s)', $subSelectQb->getSql()), 'conv', 'conv.latest_message_id = m.id');

        if ($limit !== null) {
            $qb->setMaxResults($limit)
                ->setFirstResult($offset ?? 0);
        }

        /** @var Statement $result */
        $result = $qb->execute();
        $result = $result->fetchAllAssociative();

        $clientIds = [];

        $conversationIds = [];
        foreach ($result as $key => $item) {
            $conversationIds[] = $item['id'];
            $result[$key]['active'] = true;
            $result[$key]['clientTags'] = [];
            $result[$key]['isNew'] = false;
            $clientIds[] = $item['clientId'];
        }

        $isNewFields = $this->_em->getRepository(Message::class)
            ->createQueryBuilder('m')
            ->select('IDENTITY(m.conversation) conversationId, max(m.isNew) isNew')
            ->addGroupBy('m.conversation')
            ->addGroupBy('m.client')
            ->andWhere('m.conversation in (:ids)')
            ->andWhere('m.deleted = 0')
            ->andWhere('m.client is not null')
            ->setParameter('ids', $conversationIds)
            ->getQuery()
            ->getResult();

        foreach ($result as $key => $item) {
            foreach ($isNewFields as $isNewItem) {
                if ($isNewItem['conversationId'] === $item['id']) {
                    $result[$key]['isNew'] = (bool) $isNewItem['isNew'];
                }
            }
        }

        $ct = $this->_em->getRepository(ClientTag::class)
            ->createQueryBuilder('ct')
            ->select('ct.title, IDENTITY(ct.client) clientId')
            ->andWhere('ct.client in (:clientIds)')
            ->setParameter('clientIds', $clientIds)
            ->getQuery()
            ->getResult();

        foreach ($ct as $ctItem) {
            foreach ($result as $key => $item) {
                if ($item['clientId'] === $ctItem['clientId']) {
                    $result[$key]['clientTags'][] = $ctItem['title'];
                }
            }
        }

        return $result;
    }

    public function findByClient(Client $client): ?Conversation
    {
        return $this->createQueryBuilder('conv')
            ->andWhere('conv.client = :client')
            ->andWhere('conv.user = :user')
            ->setParameter('client', $client)
            ->setParameter('user', $client->getUser())
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findByClientAndUser(Client $client, User $user)
    {
        return $this->createQueryBuilder('conv')
            ->join('conv.client','c')
            ->andWhere('c.id = :client')
            ->andWhere('conv.user = :user')
            ->setParameter('client', $client)
            ->setParameter('user', $user)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getConversationsByIds(array $ids)
    {
        $qb = $this->createQueryBuilder('c');
        $result = $qb
            ->leftJoin('c.client', 'client')
            ->where($qb->expr()->in('c.client', $ids))
            ->getQuery()
            ->getResult();

        return collect($result)
            ->keyBy(function($conversation) {
                return $conversation->getClient()->getId();
            });
    }
}
