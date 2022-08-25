<?php

namespace AppBundle\Command\Client\ClientStatus;

use AppBundle\Command\CommandBase;
use AppBundle\Entity\Client;
use Doctrine\ORM\EntityManagerInterface;
use DoctrineBatchUtils\BatchProcessing\SimpleBatchIteratorAggregate;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use AppBundle\Event\ClientMadeChangesEvent;
use AppBundle\Entity\Event;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class CheckForMissingCheckinCommand extends CommandBase
{
    private EntityManagerInterface $em;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(EntityManagerInterface $em, EventDispatcherInterface $eventDispatcher)
    {
        $this->em = $em;
        $this->eventDispatcher = $eventDispatcher;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('zf:check:for:missing:checkin')
            ->setDescription('Check for missing checkin.')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $em = $this->em;
        $timestamp = strtotime('yesterday midnight');
        $yesterday = (new \DateTime())->setTimestamp($timestamp);
        $yesterdayNo = $yesterday->format('N');

        $clientsQuery = $em
            ->getRepository(Client::class)
            ->createQueryBuilder('c')
            ->where('c.bodyProgressUpdated <= :yesterday')
            ->orWhere('c.bodyProgressUpdated IS NULL')
            ->andWhere('c.deleted = 0')
            ->andWhere('c.active = 1')
            ->andWhere('c.demoClient = 0')
            ->andWhere('c.dayTrackProgress = :yesterdayNo')
            ->setParameters([
                'yesterdayNo' => $yesterdayNo,
                'yesterday' => $yesterday
            ])
            ->getQuery();

        $clients = SimpleBatchIteratorAggregate::fromQuery($clientsQuery, 100);

        /** @var Client $client */
        foreach($clients as $client) {
            $event = new ClientMadeChangesEvent($client, Event::MISSING_CHECKIN);
            $this->eventDispatcher->dispatch($event, Event::MISSING_CHECKIN);
        }

        return 0;
    }
}
