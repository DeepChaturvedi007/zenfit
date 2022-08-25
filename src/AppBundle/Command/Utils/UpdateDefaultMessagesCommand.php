<?php

namespace AppBundle\Command\Utils;

use AppBundle\Entity\DefaultMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use AppBundle\Command\CommandBase;

class UpdateDefaultMessagesCommand extends CommandBase
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
            ->setName('zf:update:default:messages:command')
            ->setDescription('Update default messages command');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $em = $this->em;
        $defaultMessages = $em->getRepository(DefaultMessage::class)
            ->createQueryBuilder('dm')
            ->getQuery()
            ->getResult();

        foreach($defaultMessages as $dm) {
          $msg = $dm->getMessage();
          $newMsg = str_replace('[client]','<span class="client"></span>',$msg);
          $newMsg = str_replace('<a href="[url]">[url]</a>','<a class="url"></a>',$newMsg);
          $newMsg = str_replace('<a href="[checkout]">[checkout]</a>','<a class="checkout"></a>',$newMsg);

          $newDm = clone $dm;
          $newDm
            ->setMessage($newMsg);

          $em->persist($newDm);
        }

        $em->flush();

        return 0;
    }

}
