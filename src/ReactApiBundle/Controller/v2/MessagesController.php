<?php

namespace ReactApiBundle\Controller\v2;

use AppBundle\Services\QueueService;
use ChatBundle\Entity\Conversation;
use ChatBundle\Entity\Message;
use ChatBundle\Services\ChatService;
use ChatBundle\Transformer\ChatMessageTransformer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use ReactApiBundle\Controller\Controller as sfController;
use AppBundle\Event\ClientMadeChangesEvent;
use AppBundle\Entity\Event;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use AppBundle\Repository\ClientRepository;

/**
 * @Route("/v2/messages")
 */
class MessagesController extends sfController
{
    private QueueService $queueService;
    private ChatService $chatService;
    private EventDispatcherInterface $eventDispatcher;
    private TranslatorInterface $translator;

    public function __construct(
        QueueService $queueService,
        EntityManagerInterface $em,
        TranslatorInterface $translator,
        EventDispatcherInterface $eventDispatcher,
        ChatService $chatService,
        ClientRepository $clientRepository
    ) {
        $this->queueService = $queueService;
        $this->chatService = $chatService;
        $this->eventDispatcher = $eventDispatcher;
        $this->translator = $translator;

        parent::__construct($em, $clientRepository);
    }

    /**
     * @Method({"GET"})
     * @Route("")
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getAction(Request $request)
    {
        $client = $this->requestClient($request);

        if (!$client) {
            return new JsonResponse(null, JsonResponse::HTTP_UNAUTHORIZED);
        }

        $limit = $request->query->getInt('limit', 20);
        $offset = $request->query->getInt('offset', 0);

        if ($limit < 0) $limit = 20;
        if ($offset < 0) $offset = 0;

        $service = $this->chatService;

        try {
            $messages = $service->getMessages($client, $offset, $limit, false, false, true);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => $e->getMessage(),
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new JsonResponse($messages);
    }

    /**
     * @Method({"POST"})
     * @Route("")
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function postAction(Request $request)
    {
        $client = $this->requestClient($request);

        if (!$client) {
            return new JsonResponse(null, JsonResponse::HTTP_UNAUTHORIZED);
        }

        $service = $this->chatService;
        $input = $this->requestInput($request);
        $content = $input->content ?? '';
        $content = preg_replace("/\r|\n/", "<br />", $content);

        try {
            $message = $service->sendMessage(
                $content,
                $client,
                $client->getUser(),
            );

            $dispatcher = $this->eventDispatcher;
            $event = new ClientMadeChangesEvent($client, Event::SENT_MESSAGE);
            $dispatcher->dispatch($event, Event::SENT_MESSAGE);

            //notify trainer about new message
            //if they have enabled email notifications
            $user = $client->getUser();
            if ($user->getUserSettings()->getReceiveEmailOnNewMessage()) {
                $queueService = $this->queueService;
                $url = $queueService->getAbsoluteUrl('chatOverview', ['client' => $client->getId()]);

                $translator = $this->translator;
                $emailMsg = "{$translator->trans('emails.user.newMessage.body', ['%client%' => $client->getName()])}<br /><br />
                    <a href=$url>{$translator->trans('emails.user.newMessage.cta')}</a><br /><br />
                ";

                $queueService
                    ->sendEmailToTrainer(
                        $emailMsg,
                        $translator->trans('emails.user.newMessage.subject'),
                        $user->getEmail(),
                        $user->getName()
                    );
            }

            return new JsonResponse((new ChatMessageTransformer())->transform($message['msg']));
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => $e->getMessage(),
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @Method({"PATCH"})
     * @Route("/seen")
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function seenAction(Request $request)
    {
        $client = $this->requestClient($request);

        if (!$client) {
            return new JsonResponse(null, JsonResponse::HTTP_UNAUTHORIZED);
        }

        try {
            $conversation = $this
                ->em
                ->getRepository(Conversation::class)
                ->findByClient($client);

            if ($conversation === null) {
                throw new NotFoundHttpException('Client has no conversation');
            }

            $this
                ->em
                ->getRepository(Message::class)
                ->markAsSeenByClient($client, $conversation);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => $e->getMessage(),
            ], 500);
        }

        return new JsonResponse([]);
    }
}
