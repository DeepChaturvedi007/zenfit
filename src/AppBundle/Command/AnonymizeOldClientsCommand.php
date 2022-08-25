<?php declare(strict_types=1);

namespace AppBundle\Command;

use AppBundle\Entity\Client;
use AppBundle\Services\ClientService;
use Doctrine\ORM\EntityManagerInterface;
use DoctrineBatchUtils\BatchProcessing\SimpleBatchIteratorAggregate;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AnonymizeOldClientsCommand extends CommandBase
{
    private EntityManagerInterface $em;
    private ClientService $clientService;

    public function __construct(EntityManagerInterface $em, ClientService $clientService)
    {
        $this->em = $em;
        $this->clientService = $clientService;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('zf:client:anonymize')
            ->setDescription('Anonymize clients data that are > 12 month old');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $deletedYearAgoClientsQuery = $this->em
            ->getRepository(Client::class)
            ->createQueryBuilder('c')
            ->andWhere('c.deleted = 1 and c.deletedAt <= :yearAgo')
            //->andWhere('c.deleted = 1 and c.createdAt <= :yearAgo')
            ->andWhere("c.email not like '%anonzenfit%'")
            ->andWhere('c.demoClient = 0')
            ->setParameter('yearAgo', new \DateTime('-1 year'))
            ->getQuery();

        foreach (SimpleBatchIteratorAggregate::fromQuery($deletedYearAgoClientsQuery, 100) as $deletedClient) {
            $output->writeln('Anonymizing '. $deletedClient->getId() . ' ' .$deletedClient->getEmail());
            $this->clientService->anonymizeClient($deletedClient);
        }

        return 0;
    }
}
