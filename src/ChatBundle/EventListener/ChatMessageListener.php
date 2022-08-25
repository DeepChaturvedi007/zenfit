<?php

namespace ChatBundle\EventListener;

use AppBundle\Entity\Client;
use AppBundle\Entity\User;
use AppBundle\Services\ErrorHandlerService;
use ChatBundle\Repository\MessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use AppBundle\Enums\ChatMessageStatus;
use AppBundle\Services\PusherService;
use AppBundle\Services\PushNotificationService;
use AppBundle\PushMessages\PushNotificationServiceException;
use ChatBundle\Entity\Conversation;
use ChatBundle\Entity\Message;
use ChatBundle\Event\ChatMessageEvent;
use ChatBundle\Transformer\ChatMessageTransformer;
use AppBundle\Event\ClientMadeChangesEvent;
use AppBundle\Entity\Event;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ChatMessageListener
{
    private EntityManagerInterface $em;
    private PusherService $pusher;
    private PushNotificationService $pushNotification;
    private EventDispatcherInterface $eventDispatcher;
    private TranslatorInterface $translator;
    private ErrorHandlerService $errorHandlerService;
    private MessageRepository $messageRepository;

    public function __construct(
        MessageRepository $messageRepository,
        EntityManagerInterface $em,
        TranslatorInterface $translator,
        ErrorHandlerService $errorHandlerService,
        PusherService $pusher,
        EventDispatcherInterface $eventDispatcher,
        PushNotificationService $pushNotification
    )
    {
        $this->pusher = $pusher;
        $this->pushNotification = $pushNotification;
        $this->em = $em;
        $this->eventDispatcher = $eventDispatcher;
        $this->translator = $translator;
        $this->errorHandlerService = $errorHandlerService;
        $this->messageRepository = $messageRepository;
    }

    public function onMessageDeliver(ChatMessageEvent $event): void
    {
        $this->messageRepository
            ->createQueryBuilder('m')
            ->update()
            ->set('m.video', ':video')
            ->set('m.status', ':status')
            ->andWhere('m.id IN (:ids)')
            ->setParameter('ids', $event->getIds())
            ->setParameter('video', $event->getVideo())
            ->setParameter('status', $event->getError() ? ChatMessageStatus::FAILED : ChatMessageStatus::DELIVERED)
            ->getQuery()
            ->execute();

        $messages = $this->messageRepository->findBy(['id' => $event->getIds()]);

        foreach ($messages as $message) {
            try {
                //This is here because update query executed above is not reflected in Message objects yet, findBy method just returned objects from doctrine storage
                $message->setStatus($event->getError() ? ChatMessageStatus::FAILED : ChatMessageStatus::DELIVERED);

                $this->handleSentMessage($message, $event);
            } catch (\Exception $e) {
                $this->errorHandlerService->captureException($e);
                if ($this->em->isOpen()) {
                    $message->setStatus(ChatMessageStatus::FAILED);
                    $this->em->flush();
                } else {
                    break;
                }
            }
        }
    }

    private function handleSentMessage(Message $message, ChatMessageEvent $event): void
    {
        /**
         * @var Conversation $conversation
         * @var Client $client
         * @var User $user
         */
        $conversation = $message->getConversation();
        $client = $conversation->getClient();
        $user = $conversation->getUser();
        $transformer = new ChatMessageTransformer();

        $this->pusher
            ->client()
            ->trigger('messages.trainer.' . $user->getId(), 'message', $transformer->transform($message));
        $this->pusher
            ->client()
            ->trigger(
                'messages.unread.count.trainer.' . $user->getId(), 'message',
                ['count' => $user->unreadMessagesCount(), 'clientId' => $client->getId()]
            );

        if (!$message->getIsProgress()) {
            $this->pusher
                ->client()
                ->trigger('messages.client.' . $client->getId(), 'message', $transformer->transform($message));
        }

        // if trainer sent message + its not a bulk message
        if(!$message->getClient()) {
            if ($event->isBulkMessage()) {
                $clientMadeChangesEvent = new ClientMadeChangesEvent($client, Event::TRAINER_SENT_BULK_MESSAGE);
                $this->eventDispatcher->dispatch($clientMadeChangesEvent, Event::TRAINER_SENT_BULK_MESSAGE);
            } else {
                $clientMadeChangesEvent = new ClientMadeChangesEvent($client, Event::TRAINER_REPLIED_MESSAGE);
                $this->eventDispatcher->dispatch($clientMadeChangesEvent, Event::TRAINER_REPLIED_MESSAGE);
            }
        }

        if ($event->hasPushNotification()) {
            $trainer = $conversation->getUser();

            $file = $message->getVideo();
            if ($file === null) {
                $content = $message->getContent();
                if ($content !== null) {
                    $content = trim(strip_tags($content));
                }
            } else {
                $pathinfo = pathinfo($file);
                if (array_key_exists('extension', $pathinfo) && $pathinfo['extension'] === 'mp3') {
                    $content = $this->translator->trans('client.notifications.voice', [], null, $client->getLocale());
                } else {
                    $content = $this->translator->trans('client.notifications.video', [], null, $client->getLocale());
                }
            }

            if ($content !== null) {
                try {
                    $this->pushNotification->sendToClient($client, $content, [
                        'headings' => [
                            'en' => $trainer->getEmailName() ?: 'Hey buddy',
                        ],
                        'ios_badgeType' => 'Increase',
                        'ios_badgeCount' => 1,
                    ]);
                } catch (PushNotificationServiceException $e) {
                }
            }
        }
    }
}
