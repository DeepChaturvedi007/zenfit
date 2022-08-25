<?php declare(strict_types=1);

namespace AppBundle\Consumer;

use AppBundle\Enums\ChatMessageStatus;
use AppBundle\Services\ErrorHandlerService;
use Carbon\Carbon;
use ChatBundle\Entity\Message;
use ChatBundle\Event\ChatMessageEvent;
use ChatBundle\Repository\MessageRepository;
use Psr\Log\LoggerInterface;
use ChatBundle\Services\ChatService;
use AppBundle\Repository\ClientRepository;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ChatMultipleHandler implements MessageHandlerInterface
{
    private ChatService $chatService;
    private LoggerInterface $logger;
    private ErrorHandlerService $errorHandlerService;
    private ClientRepository $clientRepository;
    private MessageRepository $messageRepository;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        MessageRepository $messageRepository,
        EventDispatcherInterface $eventDispatcher,
        ChatService $chatService,
        ErrorHandlerService $errorHandlerService,
        LoggerInterface $logger,
        ClientRepository $clientRepository
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->messageRepository = $messageRepository;
        $this->chatService = $chatService;
        $this->errorHandlerService = $errorHandlerService;
        $this->logger = $logger;
        $this->clientRepository = $clientRepository;
    }

    public function __invoke(ChatMultipleEvent $event): void
    {
        $clientIds          = $event->getClientIds();
        $pushNotification   = $event->getSendPush();
        $content            = $event->getMessage();
        $media              = $event->getMedia();

        try {
            $this->logger->info('Starting - Send Msg to Multiple Clients');

            $messages = [];
            foreach ($clientIds as $id) {
                $client = $this
                    ->clientRepository
                    ->find($id);

                if ($client === null) {
                    continue;
                }

                $conversation = $this
                    ->chatService
                    ->getConversation($client);

                $content = empty($content) ? null : trim($content);

                if ($conversation->getDeleted()) {
                    $conversation->setDeleted(false);
                }

                $user = $client->getUser();
                if ($user !== null && $user->isAssistant()) {
                    $user = $user->getGymAdmin();
                }

                $message = new Message($conversation, new \DateTime());
                $message
                    ->setContent($content)
                    ->setUser($user)
                    ->setIsNew(true)
                    ->setIsProgress(false)
                    ->setFeedbackGiven(false)
                    ->setVideo($media)
                    ->setStatus(ChatMessageStatus::PENDING);

                $this->messageRepository->persist($message);
                $messages[] = $message;
            }

            if ($messages === []) {
                return;
            }

            $this->messageRepository->flush();

            $messageIds = array_map(static function (Message $message) { return (int) $message->getId();}, $messages);

            if($media) {
                $this
                    ->chatService
                    ->initMediaCompress($messageIds, true, $media, true);
            } else {
                $this->eventDispatcher->dispatch(
                    new ChatMessageEvent($messageIds, $pushNotification, null, true),
                    ChatMessageEvent::NAME
                );
            }

            $this->logger->info('Done!');
        } catch (\Exception $e) {
            $this->errorHandlerService->captureException($e);
        }
    }
}
