<?php

namespace ChatBundle\Services;

use AppBundle\Consumer\VideoCompressEvent;
use AppBundle\Consumer\VoiceCompressEvent;
use AppBundle\Enums\ChatMessageStatus;
use AppBundle\Transformer\Serializer\SimpleArraySerializer;
use Carbon\Carbon;
use ChatBundle\Event\ChatMessageEvent;
use ChatBundle\Transformer\ChatMessageTransformer;
use Doctrine\ORM\EntityManagerInterface;
use ChatBundle\Entity\Message;
use ChatBundle\Repository\ConversationRepository;
use ChatBundle\Repository\MessageRepository;
use ChatBundle\Entity\Conversation;
use AppBundle\Entity\Client;
use AppBundle\Entity\User;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use League\Fractal;

class ChatService
{
    protected EntityManagerInterface $em;
    private EventDispatcherInterface $eventDispatcher;
    private MessageBusInterface $messageBus;
    private MessageRepository $messageRepository;
    private ConversationRepository $conversationRepository;

    public function __construct(
        EntityManagerInterface $em,
        MessageBusInterface $messageBus,
        EventDispatcherInterface $eventDispatcher,
        MessageRepository $messageRepository,
        ConversationRepository $conversationRepository

    ) {
        $this->em = $em;
        $this->eventDispatcher = $eventDispatcher;
        $this->messageBus = $messageBus;
        $this->messageRepository = $messageRepository;
        $this->conversationRepository = $conversationRepository;
    }

    /** @return array<mixed> */
    public function getMessages(Client $client, int $offset = 0, int $limit = 20, bool $fetchUpdates = false, bool $pagination = false, bool $stripTags = false)
    {
        $query = $this->getMessagesQuery($client, $offset, $limit, $fetchUpdates);

        if ($pagination) {
            return new Paginator($query, $fetchJoinCollection = true);
        }

        $messages = $query
            ->getQuery()
            ->getResult();

        return $this->serializeMessages($messages, $stripTags);
    }

    /**
     * @param Client $client
     *
     * @return mixed
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function seenMessagesByTrainer(Client $client)
    {
        return $this
            ->messageRepository
            ->markAsSeenByTrainer($client);
    }

    /** @return array{msg: Message} */
    public function sendMessage(
        ?string $content,
        ?Client $client = null,
        ?User $user = null,
        Conversation $conversation = null,
        bool $isProgress = false,
        bool $pushNotification = false,
        bool $feedbackGiven = false,
        ?string $media = null,
        ?\DateTime $createdAt = null,
        bool $compressMediaImmediately = true
    ): array {
        $content = empty($content) ? null : trim($content);

        if (!$conversation) {
            if ($client === null) {
                throw new \RuntimeException('Please provide client');
            }

            $conversation = $this->getConversation($client);
        }

        if ($client && $conversation->getDeleted()) {
            $conversation->setDeleted(false);
        }

        if ($user !== null && $user->isAssistant()) {
            $user = $user->getGymAdmin();
        }

        $message = new Message($conversation, $createdAt ?: new Carbon());
        $message
            ->setContent($content)
            ->setClient($client)
            ->setUser($user)
            ->setIsNew(true)
            ->setIsProgress($isProgress)
            ->setFeedbackGiven($feedbackGiven)
            ->setVideo($media)
            ->setStatus(ChatMessageStatus::PENDING);

        $this->em->persist($message);
        $this->em->flush();

        $messageId = $message->getId();
        if ($messageId === null) {
            throw new \RuntimeException();
        }

        if ($media && $compressMediaImmediately) {
            $this->initMediaCompress([$messageId], $pushNotification, $media);
        } else if (!$media) {
            $event = new ChatMessageEvent([$messageId], $pushNotification && $user, null);
            $this->eventDispatcher->dispatch($event, ChatMessageEvent::NAME);
        }

        return ['msg' => $message];
    }

    /** @param int[] $ids */
    public function initMediaCompress(array $ids, bool $pushNotification, string $media, bool $isBulkMessage = false): void
    {
        $pathinfo = pathinfo($media);
        if (array_key_exists('extension', $pathinfo) && $pathinfo['extension'] === 'wav') {
            $this->messageBus->dispatch(new VoiceCompressEvent($ids, $pushNotification, $media, $isBulkMessage));
        } else {
            $this->messageBus->dispatch(new VideoCompressEvent($ids, $pushNotification, $media, $isBulkMessage));
        }
    }

    public function getConversation(Client $client): Conversation
    {
        $conversation = $this
            ->conversationRepository
            ->findByClient($client);

        if ($conversation === null) {
            $conversation = new Conversation($client->getUser(), $client);

            $this->em->persist($conversation);
            $this->em->flush();
        }

        return $conversation;
    }

    /**
     * @param \Doctrine\Common\Collections\Collection|array $messages
     * @param boolean $stripTags
     *
     * @return array
     */
    public function serializeMessages($messages, $stripTags = false)
    {
        $fractal = new Fractal\Manager();
        $resource = new Fractal\Resource\Collection($messages, new ChatMessageTransformer($stripTags));

        return $fractal
            ->setSerializer(new SimpleArraySerializer)
            ->createData($resource)
            ->toArray();
    }

    /**
     * @param Client $client
     * @param int $offset
     * @param int $limit
     * @param bool $fetchUpdates
     *
     * @return \Doctrine\ORM\QueryBuilder
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMException
     */
    private function getMessagesQuery(Client $client, $offset = 0, $limit = 20, $fetchUpdates = false)
    {
        $conversation = $this->getConversation($client);
        $query = $this
            ->messageRepository
            ->createQueryBuilder('ms')
            ->where('ms.conversation = :id')
            ->andWhere('ms.deleted = 0');

        if (!$fetchUpdates) {
            $query->andWhere('ms.isProgress = 0');
        }

        $query
            ->setParameter('id', $conversation->getId())
            ->orderBy('ms.id', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        return $query;
    }
}
