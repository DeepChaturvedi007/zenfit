<?php

namespace AppBundle\Command\Client\ClientStatus;

use AppBundle\Command\CommandBase;
use AppBundle\Repository\ClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use AppBundle\Event\ClientMadeChangesEvent;
use AppBundle\Entity\Event;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class CheckForMissingCommunicationCommand extends CommandBase
{
    private EventDispatcherInterface $eventDispatcher;
    private ClientRepository $clientRepository;
    private EntityManagerInterface $em;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        EntityManagerInterface $em,
        ClientRepository $clientRepository
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->clientRepository = $clientRepository;
        $this->em = $em;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('zf:check:for:missing:communication')
            ->setDescription('Check for missing communication between trainer & client.');
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $clientsQuery = $this
            ->clientRepository
            ->createQueryBuilder('c')
            ->distinct()
            ->select('c.id, us.oldChatsInterval')
            ->innerJoin('c.conversations', 'conv')
            ->innerJoin('c.user', 'u')
            ->innerJoin('u.userSettings', 'us')
            ->innerJoin('conv.messages', 'm')
            ->andWhere('c.deleted = 0')
            ->andWhere('c.active = 1')
            ->andWhere('c.demoClient = 0')
            ->addOrderBy('c.id')
            ->groupBy('c.id')
            ->andHaving("max(m.sentAt) < DATE_SUB(CURRENT_TIMESTAMP(), us.oldChatsInterval, 'DAY')")
            ->getQuery();

        foreach($clientsQuery->getArrayResult() as $client) {
            $client = $this->clientRepository->find($client['id']);
            if ($client === null) {
                continue;
            }
            echo $client->getId();

            $dispatcher = $this->eventDispatcher;
            $event = new ClientMadeChangesEvent($client, Event::MISSING_COMMUNICATION);
            $dispatcher->dispatch($event, Event::MISSING_COMMUNICATION);

            unset($client);
            $this->em->clear();
        }

        return 0;
    }
}
