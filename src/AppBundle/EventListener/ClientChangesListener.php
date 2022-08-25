<?php

namespace AppBundle\EventListener;

use AppBundle\Entity\Event;
use AppBundle\Entity\Client;
use AppBundle\Entity\ClientStatus;
use AppBundle\Event\ClientMadeChangesEvent;
use AppBundle\Services\QueueService;
use AppBundle\Transformer\ClientStatusTransformer;
use AppBundle\Services\PusherService;
use AppBundle\Services\ClientService;
use Doctrine\ORM\EntityManagerInterface;
use AppBundle\Services\ErrorHandlerService;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ClientChangesListener
{
    public function __construct(
        private EntityManagerInterface $em,
        private PusherService $pusher,
        private ClientService $clientService,
        private ErrorHandlerService $errorHandlerService,
        private QueueService $queueService,
        private string $appHostname,
        private UrlGeneratorInterface $urlGenerator
    ) {}

    public function onClientStatusUpdate(ClientMadeChangesEvent $event): void
    {
        $em = $this->em;
        $eventEntity = $this->em
            ->getRepository(Event::class)
            ->findOneBy(['name' => $event->getName()]);

        if ($eventEntity === null) {
            throw new \RuntimeException('No event '.$event->getName() . ' in DB');
        }

        try {
            $entry = $em->getRepository(ClientStatus::class)->findOneBy([
                'event' => $eventEntity,
                'client' => $event->getClient(),
                'resolved' => false
            ]);

            if(!$entry) {
                $entry = (new ClientStatus($eventEntity, $event->getClient()));

                $em->persist($entry);
            }

            if($message = $event->getMessage()) {
                $message->setClientStatus($entry);
            }

            $em->flush();
        } catch (\Exception $e) {
            $this->errorHandlerService->captureException($e);
        }
    }

    public function onClientPaymentSucceeded(ClientMadeChangesEvent $event): void
    {
        $unresolvedEvents = [Event::PAYMENT_FAILED, Event::PAYMENT_PENDING, Event::SUBSCRIPTION_CANCELED];
        $this->getUnresolvedClientStatusEntity($event, $unresolvedEvents);
    }

    public function onClientAnsweredQuestionnaire(ClientMadeChangesEvent $event): void
    {
        $unresolvedEvents = [Event::QUESTIONNAIRE_PENDING];
        $client = $event->getClient();
        $user = $client->getUser();
        $this->getUnresolvedClientStatusEntity($event, $unresolvedEvents);
        //send email to trainer
        $url = $this->appHostname . $this->urlGenerator->generate('clientInfo', ['client' => $client->getId()]);
        $msg = "{$client->getName()} has answered your questionnaire!<br /><br />Click <a href=$url>here</a> to get started creating his/her plans!";

        $this
            ->queueService
            ->sendEmailToTrainer(
                $msg,
                'Your client answered your questionnaire!',
                $user->getEmail(),
                $user->getName()
            );
    }

    public function onClientCreatedLogin(ClientMadeChangesEvent $event): void
    {
        $unresolvedEvents = [Event::INVITE_PENDING];
        $this->getUnresolvedClientStatusEntity($event, $unresolvedEvents);
    }

    public function onTrainerRepliedClientMessage(ClientMadeChangesEvent $event): void
    {
        $unresolvedEvents = [Event::SENT_MESSAGE, Event::UPDATED_BODYPROGRESS, Event::UPLOADED_IMAGE, Event::MISSING_COMMUNICATION];
        $this->getUnresolvedClientStatusEntity($event, $unresolvedEvents);
        $this->updateUnreadMessagePusher($event->getClient(), 0);
    }

    public function onTrainerSentBulkMessage(ClientMadeChangesEvent $event): void
    {
        $unresolvedEvents = [Event::MISSING_COMMUNICATION];
        $this->getUnresolvedClientStatusEntity($event, $unresolvedEvents);
        $this->updateUnreadMessagePusher($event->getClient(), 0);
    }

    public function onTrainerSentPaymentLink(ClientMadeChangesEvent $event): void
    {
        $unresolvedEvents = [Event::SUBSCRIPTION_CANCELED];
        $this->getUnresolvedClientStatusEntity($event, $unresolvedEvents);
    }

    public function onTrainerDeactivatedClient(ClientMadeChangesEvent $event): void
    {
        $unresolvedEvents = [];
        $this->deleteClient($event->getClient());
        $this->getUnresolvedClientStatusEntity($event, $unresolvedEvents);
    }

    public function onTrainerExtendedClient(ClientMadeChangesEvent $event): void
    {
        $unresolvedEvents = [Event::ENDING_SOON, Event::COMPLETED];
        $this->getUnresolvedClientStatusEntity($event, $unresolvedEvents);
    }

    public function onClientEnded(ClientMadeChangesEvent $event): void
    {
        $unresolvedEvents = [Event::ENDING_SOON];
        $this->getUnresolvedClientStatusEntity($event, $unresolvedEvents);
    }

    public function onClientCheckedIn(ClientMadeChangesEvent $event): void
    {
        $unresolvedEvents = [Event::MISSING_CHECKIN, Event::INVITE_PENDING];
        $this->getUnresolvedClientStatusEntity($event, $unresolvedEvents);
    }

    public function onTrainerUpdatedClientWorkoutPlan(ClientMadeChangesEvent $event): void
    {
        $unresolvedEvents = [Event::TRAINER_CREATE_WORKOUT_PLAN, Event::TRAINER_UPDATE_WORKOUT_PLAN];
        $this->getUnresolvedClientStatusEntity($event, $unresolvedEvents);
    }

    public function onTrainerUpdatedClientMealPlan(ClientMadeChangesEvent $event): void
    {
        $unresolvedEvents = [Event::TRAINER_CREATE_MEAL_PLAN, Event::TRAINER_UPDATE_MEAL_PLAN];
        $this->getUnresolvedClientStatusEntity($event, $unresolvedEvents);
    }

    public function onTrainerActivatedClient(ClientMadeChangesEvent $event): void
    {
        $unresolvedEvents = [Event::NEED_WELCOME];
        $this->activateClient($event->getClient());
        $this->getUnresolvedClientStatusEntity($event, $unresolvedEvents);
    }

    public function onClientSentMessage(ClientMadeChangesEvent $event): void
    {
        //used to ensure that all invite pending events are resolved
        //client cannot send message if they havent already created a login.
        $unresolvedEvents = [Event::INVITE_PENDING, Event::MISSING_COMMUNICATION];
        $this->getUnresolvedClientStatusEntity($event, $unresolvedEvents);
    }

    public function onTrainerMarkedMessageAsUnread(ClientMadeChangesEvent $event): void
    {
        $this->updateUnreadMessagePusher($event->getClient(), 1);
    }

    private function getUnresolvedClientStatusEntity(ClientMadeChangesEvent $event, $unresolvedEvents): void
    {
        $client = $event->getClient();
        if(count($unresolvedEvents) === 0) {
            //update all events to resolved
            $entries = $this
                ->em
                ->getRepository(ClientStatus::class)
                ->findBy([
                    'client' => $client,
                    'resolved' => false
                ]);

            foreach($entries as $entry) {
                $entry
                    ->setResolved(true)
                    ->setResolvedBy(new \DateTime('now'));
            }
        }

        foreach($unresolvedEvents as $unresolvedEvent) {
            $eventEntity = $this
                ->em
                ->getRepository(Event::class)
                ->findOneBy(['name' => $unresolvedEvent]);

            if ($eventEntity === null) {
                throw new \RuntimeException("Missing Event entity in ClientChangesListener: {$unresolvedEvent}");
            }

            $entry = $this
                ->em
                ->getRepository(ClientStatus::class)
                ->findOneBy([
                    'event' => $eventEntity,
                    'client' => $client,
                    'resolved' => false
                ]);

            if ($entry !== null) {
                $entry
                    ->setResolved(true)
                    ->setResolvedBy(new \DateTime('now'));

                $transformer = new ClientStatusTransformer();
                $this
                    ->pusher
                    ->client()
                    ->trigger('clientStatus.trainer.' . $client->getUser()->getId(), 'clientStatus', $transformer->transform($entry, $eventEntity));
            }
        }

        $this->em->flush();
    }

    private function updateUnreadMessagePusher(Client $client, $count): void
    {
        $this->pusher
            ->client()
            ->trigger(
                'messages.unread.count.trainer.' . $client->getUser()->getId(), 'message',
                ['count' => $count, 'clientId' => $client->getId()]
            );
    }

    private function activateClient(Client $client): void
    {
        $client->setAccessApp(true);
        $this->em->flush();
    }

    private function deleteClient(Client $client): void
    {
        try {
            $clientId = $client->getId();
            if ($clientId !== null) {
                $this->clientService->deleteClients([$clientId]);
            }
        } catch (\Throwable $e) {}
    }

}
