<?php

namespace AppBundle\Command\Utils;

use AppBundle\Entity\Client;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use AppBundle\Command\CommandBase;

class UpdateClientEndDateCommand extends CommandBase
{
    public function __construct(private EntityManagerInterface $em)
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('utils:update:client:end:date')
            ->setDescription('Update client end date');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $em = $this->em;
        $clients = $em->getRepository(Client::class)->findBy([
            'deleted' => false
        ]);

        foreach($clients as $client) {
            $duration = $client->getDuration();
            $startDate = $client->getStartDate();
            $start =  $startDate ? new \DateTime($startDate->format('Y-m-d')) : null;

            if(!$start) continue;
            if($duration == 0) continue;

            $endDate = $start->modify("+$duration month");
            $client->setEndDate($endDate);
        }

        $em->flush();

        return 0;
    }

}
