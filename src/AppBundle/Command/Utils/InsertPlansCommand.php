<?php

namespace AppBundle\Command\Utils;

use AppBundle\Entity\Bundle;
use AppBundle\Entity\Client;
use AppBundle\Entity\Payment;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use AppBundle\Command\CommandBase;
use AppBundle\Entity\Plan;

class InsertPlansCommand extends CommandBase
{
    private EntityManagerInterface $em;
    private string $projectRoot;

    public function __construct(EntityManagerInterface $em, string $projectRoot)
    {
        $this->em = $em;
        $this->projectRoot = $projectRoot;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('zf:insert:plans:command')
            ->setDescription('Insert Plans Command');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $em = $this->em;
        $file = $this->projectRoot . '/' . 'rene.csv';
        $plans = $this->parseCSV($file);

        foreach ($plans as $plan) {

            $bundle = $em->getRepository(Bundle::class)->find($plan['bundle']);
            if ($bundle === null) {
                continue;
            }
            $payment = $em->getRepository(Payment::class)->find($plan['payment']);
            $client = $em->getRepository(Client::class)->find($plan['client']);

            $planEntity = new Plan();
            $planEntity
                ->setType(0)
                ->setClient($client)
                ->setPayment($payment)
                ->setBundle($bundle)
                ->setTitle($bundle->getName())
                ->setCreatedAt(new \DateTime($plan['created']));

            $em->persist($planEntity);
        }

        $em->flush();

        return 0;
    }

    private function parseCSV($file)
    {
        $exercises = [];
        $row = 1;
        if (($handle = fopen($file, "r")) !== FALSE) {
          while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $row++;

            if($row == 2) continue;
            $exercises[] = [
              'client' => $data[0],
              'payment' => $data[1],
              'bundle' => $data[2],
              'created' => $data[3],
            ];
          }

          fclose($handle);
        }

        return $exercises;
    }
}
