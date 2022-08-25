<?php

namespace TrainerBundle\Controller;

use AppBundle\Entity\Client;
use AppBundle\Entity\ClientStatus;
use AppBundle\Services\QueueService;
use ChatBundle\Services\ChatService;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use AppBundle\Entity\Queue;
use AppBundle\Entity\Event;
use AppBundle\Entity\DefaultMessage;
use AppBundle\Event\ClientMadeChangesEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/api/client-status")
 */
class ClientStatusController extends Controller
{
    private QueueService $queueService;
    private string $appHostname;
    private EventDispatcherInterface $eventDispatcher;
    private ChatService $chatService;
    private TranslatorInterface $translator;

    public function __construct(
        QueueService $queueService,
        EntityManagerInterface $em,
        string $appHostname,
        ChatService $chatService,
        TranslatorInterface $translator,
        EventDispatcherInterface $eventDispatcher,
        TokenStorageInterface $tokenStorage
    ) {
        $this->queueService = $queueService;
        $this->translator = $translator;
        $this->appHostname = $appHostname;
        $this->eventDispatcher = $eventDispatcher;
        $this->chatService = $chatService;

        parent::__construct($em, $tokenStorage);
    }

    /**
     * @Route("/take-action", name="takeActionOnClientStatus")
     * @Method({"POST"})
     * @param Request $request
     * @return JsonResponse
     */
    public function takeAction(Request $request)
    {
        $client = $this
            ->getEm()
            ->getRepository(Client::class)
            ->find($request->request->get('clientId'));

        if ($client === null) {
            throw new NotFoundHttpException('Client not found');
        }

        $dispatcher = $this->eventDispatcher;
        $res = [];

        switch ($request->request->get('type')) {
          case DefaultMessage::TYPE_WELCOME_MESSAGE:
            $event = new ClientMadeChangesEvent($client, Event::TRAINER_ACTIVATED_CLIENT);
            $dispatcher->dispatch($event, Event::TRAINER_ACTIVATED_CLIENT);
            $res['msg'] = 'Client activated and will be moved to your active clients. Remember to send a welcome message.';
            break;
          case DefaultMessage::TYPE_CLIENT_ENDING_MESSAGE:
            $event = new ClientMadeChangesEvent($client, Event::TRAINER_DEACTIVATED_CLIENT);
            $dispatcher->dispatch($event, Event::TRAINER_DEACTIVATED_CLIENT);
            $res['msg'] = 'Client deleted, please refresh page.';
            break;
        }

        $this->getEm()->flush();
        return new JsonResponse($res);
    }

    /**
     * @Route("/ignore", name="ignoreClientStatus")
     * @Method({"POST"})
     * @param Request $request
     * @return JsonResponse
     */
    public function ignoreAction(Request $request)
    {
        $em = $this->getEm();
        $repo = $em->getRepository(ClientStatus::class);
        $clientStatus = $repo->find($request->request->get('id'));

        if(!$clientStatus) {
            return new JsonResponse([
                'success' => false,
                'error' => 'No clientStatus entity was found.'
            ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        $clientStatus
            ->setResolved(true)
            ->setResolvedBy(new \DateTime('now'));

        $em->flush();

        $newStatus = $repo->getStatusByClient($clientStatus->getClient());
        return new JsonResponse([
            'status' => $newStatus
        ]);
    }

    /**
     * @Route("/resendQuestionnaire", name="resendQuestionnaire")
     * @Method({"POST"})
     * @param Request $request
     * @return JsonResponse
     * @throws OptimisticLockException
     */
    public function resendQuestionnaireAction(Request $request)
    {
        $em = $this->getEm();
        $client = $em->getRepository(Client::class)
            ->find($request->request->get('client'));

        if ($client === null) {
            throw new NotFoundHttpException('Client not found');
        }

        $service = $this->queueService;
        $queue = $service->createClientCreationEmailQueueEntity($client);
        $hostname = $this->appHostname;
        $url = $queue->getQuestionnaireSurveyOnlyUrl($hostname);

        $translator = $this->translator;
        $translator->setLocale($client->getLocale() ?? 'en');

        $message =
            "{$translator->trans('emails.client.answerSurvey.body')}
            <br><br><a href=$url>{$translator->trans('emails.client.answerSurvey.cta')}</a></p>";

        $queue
            ->setMessage($message)
            ->setSubject($translator->trans('emails.client.answerSurvey.subject'))
            ->setStatus(Queue::STATUS_PENDING)
            ->setEmail($client->getEmail());

        $em->persist($queue);
        $em->flush();

        return new JsonResponse(['success' => true]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("/remind", name="remindClient")
     * @Method({"POST"})
     */
    public function remindAction(Request $request)
    {
        $em = $this->getEm();
        $clientId = $request->request->get('client');
        $msg = ((string) $request->request->get('msg')) ?: null;
        $clientNextStepActive = $request->request->has('client-next-step') && $request->request->get('client-next-step') != null;

        if (empty($msg)) {
            return new JsonResponse([
                'error' => 'You can\'t send an empty message.'
            ], 422);
        }

        /**
         * @var \AppBundle\Entity\Client $client
         */
        $client = $em->getRepository(Client::class)
            ->find($clientId);

        if (!$client) {
            return new JsonResponse([
                'error' => 'Non-existent client.'
            ], 422);
        }

        if($clientNextStepActive) {
            match ($request->request->get('client-next-step')) {
                'deactivate-keep-app' => $client
                    ->setActive(false)
                    ->setAccessApp(true),
                'deactivate-no-app' => $client
                    ->setActive(false)
                    ->setAccessApp(false),
                'delete' => $client
                    ->setDeleted(true),
                default => null,
            };

            $dispatcher = $this->eventDispatcher;
            $event = new ClientMadeChangesEvent($client, Event::TRAINER_DEACTIVATED_CLIENT);
            $dispatcher->dispatch($event, Event::TRAINER_DEACTIVATED_CLIENT);
        }


        $service = $this->chatService;
        $conversation = $service->getConversation($client);
        $service->sendMessage($msg, null, $client->getUser(), $conversation, false, true, false, null, new \DateTime());
        $service->seenMessagesByTrainer($client);
        $em->flush();

        return new JsonResponse([
            'success' => true,
            'reload' => $clientNextStepActive ?? false
        ]);
    }
}
