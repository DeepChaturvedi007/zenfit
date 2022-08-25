<?php

namespace AppBundle\Command\Client\ClientStatus;

use AppBundle\Command\CommandBase;
use AppBundle\Entity\Client;
use AppBundle\Services\ClientService;
use Doctrine\ORM\EntityManagerInterface;
use DoctrineBatchUtils\BatchProcessing\SimpleBatchIteratorAggregate;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use AppBundle\Event\ClientMadeChangesEvent;
use AppBundle\Entity\Event;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class UpdateClientDurationCommand extends CommandBase
{
    private EntityManagerInterface $em;
    private EventDispatcherInterface $eventDispatcher;
    private ClientService $clientService;

    public function __construct(EntityManagerInterface $em, ClientService $clientService, EventDispatcherInterface $eventDispatcher)
    {
        $this->em = $em;
        $this->eventDispatcher = $eventDispatcher;
        $this->clientService = $clientService;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('zf:update:client:duration')
            ->setDescription('Check and update client duration if they are ending soon or completed.')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $em = $this->em;

        $clientsQuery = $em->getRepository(Client::class)
            ->createQueryBuilder('c')
            ->where('c.endDate <= :in21days')
            ->andWhere('c.deleted = 0')
            ->andWhere('c.active = 1')
            ->setParameter('in21days', new \DateTime('+21 days'))
            ->getQuery();

        $clients = SimpleBatchIteratorAggregate::fromQuery($clientsQuery, 100);

        /** @var Client $client */
        foreach($clients as $client) {
            if($client->getEndDate() > new \DateTime()) {
                $eventName = Event::ENDING_SOON;
            } else {
                $eventName = Event::COMPLETED;
                $userSettings = $client->getUser()->getUserSettings();
                if ($userSettings !== null && $userSettings->isAutoDeactivate()) {
                    $client
                        ->setActive(false)
                        ->setAccessApp(false);
                    $this->clientService->unsubscribeIfActiveSubscription($client);
                }
            }

            $dispatcher = $this->eventDispatcher;
            $event = new ClientMadeChangesEvent($client, $eventName);
            $dispatcher->dispatch($event, $eventName);
        }

        return 0;
    }
}
