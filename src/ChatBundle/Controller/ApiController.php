<?php

namespace ChatBundle\Controller;

use AppBundle\Controller\Controller;
use AppBundle\Enums\ChatMessageStatus;
use AppBundle\Services\AwsService;
use AppBundle\Services\QueueService;
use ChatBundle\Entity\Message;
use ChatBundle\Repository\ConversationRepository;
use ChatBundle\Services\ChatService;
use ChatBundle\Transformer\ChatMessageTransformer;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use ChatBundle\Entity\Conversation;
use AppBundle\Entity\User;
use AppBundle\Entity\Client;
use AppBundle\Entity\Queue;
use AppBundle\Entity\Event;
use AppBundle\Event\ClientMadeChangesEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use AppBundle\Consumer\ChatMultipleEvent;

/**
 * @Route("/api")
 */
class ApiController extends Controller
{
    private QueueService $queueService;
    private ChatService $chatService;
    private EventDispatcherInterface $eventDispatcher;
    private TranslatorInterface $translator;
    private AwsService $awsService;
    private string $s3ImagesBucket;
    private MessageBusInterface $messageBus;
    private ConversationRepository $conversationRepository;
    private EntityManagerInterface $em;

    public function __construct(
        QueueService $queueService,
        ChatService $chatService,
        EventDispatcherInterface $eventDispatcher,
        TranslatorInterface $translator,
        ConversationRepository $conversationRepository,
        AwsService $awsService,
        string $s3ImagesBucket,
        EntityManagerInterface $em,
        TokenStorageInterface $tokenStorage,
        MessageBusInterface $messageBus
    ) {
        $this->queueService = $queueService;
        $this->chatService = $chatService;
        $this->translator = $translator;
        $this->awsService = $awsService;
        $this->s3ImagesBucket = $s3ImagesBucket;
        $this->eventDispatcher = $eventDispatcher;
        $this->messageBus = $messageBus;
        $this->em = $em;
        $this->conversationRepository = $conversationRepository;

        parent::__construct($em, $tokenStorage);
    }

    /**
     * @Route("/send", methods={"POST"})
     */
    public function sendMessageAction(Request $request): JsonResponse
    {
        $service = $this->chatService;

        $clientId = $request->request->get('clientId');
        $trainerId = $request->request->get('trainer') === null ? null : (int) $request->request->get('trainer');

        $currentUser = $this->getUser();

        if ($trainerId !== null && (!$currentUser instanceof User || $trainerId !== $currentUser->getId())) {
            throw new AccessDeniedHttpException();
        }

        $msg = $request->request->get('msg');
        $media = $request->request->get('media');

        $trainer = $this
            ->em
            ->getRepository(User::class)->find($trainerId);

        $client = $this
            ->em
            ->getRepository(Client::class)->find($clientId);

        if ($client === null || $trainer === null) {
            throw new NotFoundHttpException('Client or Trainer not found');
        }

        $conversation = $service->getConversation($client);

        try {
            $service->sendMessage(
                $msg,
                null,
                $trainer,
                $conversation,
                false,
                true,
                false,
                $media,
            );

            $messages = $service->getMessages($client, 0, 15, true, false);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => $e->getMessage()
            ], 422);
        } catch(\Throwable $e) {
            return new JsonResponse([
                'error' => $e->getMessage()
            ], 422);
        }

        if ($client->getAcceptEmailNotifications() && !$client->getDeleted()) {
            $translator = $this->translator;
            $translator->setLocale($client->getLocale() ?? 'en');

            $this->queueService->insertIntoEmailQueue(
                $client->getEmail(),
                $client->getName(),
                Queue::STATUS_PENDING,
                Queue::TYPE_CLIENT_MESSAGE_NOTIFICATION,
                $client,
                null,
                null,
                $translator->trans('emails.client.newMessage.subject')
            );
        }

        //dispatch trainer feedback event
        //to resolve eventual unresolved clientStatus check-ins
        $event = new ClientMadeChangesEvent($client, Event::TRAINER_REPLIED_MESSAGE);
        $this->eventDispatcher->dispatch($event, Event::TRAINER_REPLIED_MESSAGE);

        return new JsonResponse([
            'messages' => \array_reverse($messages)
        ]);
    }

    /**
     * @Route("/multiple-send{user}", methods={"POST"})
     */
    public function multipleSendMessageAction(User $user, Request $request): JsonResponse
    {
        $json = $request->getContent();
        $res = json_decode($json);

        $this->messageBus->dispatch(
            new ChatMultipleEvent(
                $res->clients,
                $res->msg,
                $res->media ?? null,
                true
            )
        );

        return new JsonResponse([
            'msg' => 'The message will be sent to the clients asap.'
        ]);
    }

    /**
     * @Route("/get-presigned-request", methods={"GET"})
     */
    public function getPresignedRequest(Request $request): JsonResponse
    {
        $service = $this->awsService;

        $contentType    = $request->query->get('contentType');
        $extension      = $request->query->get('extension');

        $filename = $service->generateKey('trainers/video-messages', $extension);

        $presignedRequest = $service
            ->createPresignedRequest(
                $this->s3ImagesBucket,
                $filename,
                $contentType
            );

        return new JsonResponse([
            'url' => $presignedRequest,
            'filename' => $filename
        ]);
    }

    /**
     * @Route("/fetch-messages/{client}", methods={"POST"})
     */
    public function fetchMessagesAction(Client $client, Request $request): JsonResponse
    {
        $limit = 15;
        $json = $request->getContent();
        $res = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        try {
            $startFrom = (int) ($res['startFrom'] ?? null);
            $messages = $this->chatService->getMessages($client, $startFrom, $limit, true, true);
            $conversation = $this
                ->chatService
                ->getConversation($client);

            $newMessagesIds = $this->em->getRepository(Message::class)
                ->createQueryBuilder('m')
                ->select('m.id')
                ->andWhere('m.client = :client and m.user = :user and m.conversation = :conversation and m.isNew = 1')
                ->setParameter('user', $client->getUser())
                ->setParameter('conversation', $conversation)
                ->setParameter('client', $client)
                ->getQuery()
                ->getArrayResult();

            $newMessagesIds = array_map(static function (array $value) {
                return $value['id'];
            }, $newMessagesIds);

            if (count($newMessagesIds) > 0) {
                $this->em->getRepository(Message::class)
                    ->createQueryBuilder('m')
                    ->update()
                    ->set('m.status', ':status')
                    ->setParameter('status', ChatMessageStatus::READ)
                    ->set('m.isNew', ':isNew')
                    ->setParameter('isNew', false)
                    ->andWhere('m.id in (:ids)')
                    ->setParameter('ids', $newMessagesIds)
                    ->getQuery()
                    ->execute();
            }

            $totalItems = count($messages);
            $messages = \array_reverse($this->chatService->serializeMessages($messages));

            return new JsonResponse([
                'messages' => $messages,
                'hasMore' => $startFrom + $limit < $totalItems
            ]);
        } catch (\Throwable $e) {
            return new JsonResponse([
                'error' => $e->getMessage()
            ], 422);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => $e->getMessage()
            ], 422);
        }

    }

    /**
     * @Route("/fetch-conversations/{user}", methods={"GET"})
     */
    public function fetchConversationsAction(User $user, Request $request): JsonResponse
    {
        $limit = $request->get('limit');
        $offset = $request->get('offset');
        if ($limit !== null) {
            $limit = (int) $limit;
        }
        if ($offset !== null) {
            $offset = (int) $offset;
        }

        $currentUser = $this->getUser();
        if (!$currentUser instanceof User || $currentUser !== $user) {
            throw new AccessDeniedHttpException('You have to be logged in and can fetch only yours conversations');
        }

        $tags = [];
        if ($currentUser->isAssistant()) {
            $user = $currentUser->getGymAdmin();
            $tags = [$currentUser->getFirstName()];
        } else {
            if ($request->query->has('tags') && $request->query->get('tags') !== '' && $request->query->get('tags') !== null) {
                $tags = explode(',', $request->query->get('tags'));
            }
        }

        /** @var ConversationRepository $convRepo */
        $convRepo = $this
            ->em
            ->getRepository(Conversation::class);

        $conversations = $convRepo
            ->findByUser($user, $request->query->get('q'), $tags, $limit, $offset);

        return new JsonResponse(compact('conversations'));
    }

    /**
     * @Route("/get-conversation/{client}", methods={"GET"})
     */
    public function getConversationAction(Client $client, Request $request): JsonResponse
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw new AccessDeniedHttpException('You have to be logged in and can fetch only yours conversations');
        }

        $tags = [];
        if ($user->isAssistant()) {
            $tags = [$user->getFirstName()];
            $user = $user->getGymAdmin();
        }

        $conversation = $this
            ->chatService
            ->getConversation($client);

        //check if conversion exists but it is deleted
        if($conversation->getDeleted()) {
            $conversation->setDeleted(false);
            $this->em->flush();
        }

        $conversations = $this->conversationRepository
            ->findByUser($user, $request->query->get('q'), $tags);

        $selectedConversation = collect($conversations)->firstWhere('id', $conversation->getId());

        return new JsonResponse(compact('selectedConversation', 'conversations'));
    }

    /**
     * @Route("/mark-unread-conversation/{conversation}", methods={"POST"})
     */
    public function markLastAsUnreadConversationAction(Conversation $conversation, Request $request): JsonResponse
    {
        $repo = $this->em->getRepository(Message::class);
        try {
            $message = $repo->markLastAsUnreadByTrainer($conversation);

            $dispatcher = $this->eventDispatcher;
            $event = new ClientMadeChangesEvent($conversation->getClient(), Event::TRAINER_MARKED_MESSAGE_UNREAD);
            $dispatcher->dispatch($event, Event::TRAINER_MARKED_MESSAGE_UNREAD);

            return new JsonResponse([
                'data' => $message ? (new ChatMessageTransformer())->transform($message) : null,
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'reason' => $e->getMessage(),
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @Route("/mark-done-conversation/{conversation}", methods={"POST"})
     */
    public function markConversationDoneAction(Conversation $conversation, Request $request): JsonResponse
    {
        try {
            $dispatcher = $this->eventDispatcher;
            $clientMadeChangesEvent = new ClientMadeChangesEvent($conversation->getClient(), Event::TRAINER_REPLIED_MESSAGE);
            $dispatcher->dispatch($clientMadeChangesEvent, Event::TRAINER_REPLIED_MESSAGE);
            return new JsonResponse([
                'success' => true
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'reason' => $e->getMessage(),
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @Route("/mark-messages-read/{client}", methods={"POST"})
     */
    public function markMessagesAsReadAction(Client $client, Request $request): JsonResponse
    {
        try {
            $conversation = $this
                ->chatService
                ->getConversation($client);

            $this->em->getRepository(Message::class)
                ->createQueryBuilder('m')
                ->update()
                ->set('m.isNew', ':isNew')
                ->setParameter('isNew', false)
                ->andWhere('m.isNew = 1')
                ->andWhere('m.conversation = :conversation')
                ->setParameter('conversation', $conversation)
                ->getQuery()
                ->execute();

            return new JsonResponse([
                'success' => true
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'reason' => $e->getMessage(),
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @Route("/delete-conversation", methods={"POST"})
     */
    public function deleteConversation(Request $request): JsonResponse
    {
        $res = json_decode($request->getContent());
        $conversation = $this->em->getRepository(Conversation::class)->find($res->id);

        if ($conversation) {
            $conversation->setDeleted(true);
            $this->em->flush();
        }

        return new JsonResponse([
            'status' => true,
        ]);
    }

    /**
     * @Route("/delete-message", methods={"POST"})
     */
    public function deleteMessage(Request $request): JsonResponse
    {
        $res = json_decode($request->getContent());
        $message = $this->em->getRepository(Message::class)->find($res->id);

        if ($message) {
            $message->setDeleted(true);
            $this->em->flush();
        }

        return new JsonResponse([
            'status' => true,
        ]);
    }
}
