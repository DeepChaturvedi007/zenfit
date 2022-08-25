<?php

namespace AppBundle\Command\Utils;

use AppBundle\Entity\ActivityLog;
use AppBundle\Entity\Client;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use AppBundle\Command\CommandBase;

class UpdateClientBodyProgressCommand extends CommandBase
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('zf:update:client:body:progress')
            ->setDescription('Update client body progress');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $em = $this->em;
        $clients = $em->getRepository(Client::class)
            ->createQueryBuilder('c')
            ->getQuery()
            ->getResult();

        foreach($clients as $client) {

          if($client->getBodyProgressUpdated()) {
            continue;
          }

          $activityLog = $em->getRepository(ActivityLog::class)
            ->createQueryBuilder('al')
            ->where('al.event = :event1')
            ->orWhere('al.event = :event2')
            ->andWhere('al.client = :client')
            ->setParameters([
              'event1' => 3,
              'event2' => 4,
              'client' => $client
            ])
            ->orderBy('al.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

            if($activityLog) {
              $date = $activityLog->getDate();
              $client->setBodyProgressUpdated($date);
            }

        }

        $em->flush();

        return 0;
    }

}
