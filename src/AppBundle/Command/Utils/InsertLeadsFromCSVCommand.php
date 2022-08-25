<?php

namespace AppBundle\Command\Utils;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use AppBundle\Command\CommandBase;
use AppBundle\Entity\Lead;
use AppBundle\Entity\User;

class InsertLeadsFromCSVCommand extends CommandBase
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
            ->setName('zf:insert:leads:from:csv')
            ->setDescription('Insert leads from csv')
            ->addArgument('file', InputArgument::REQUIRED, 'File');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $em = $this->em;
        $file = $this->projectRoot . '/' . $input->getArgument('file');

        $leads = $this->parseCSV($file);

        foreach($leads as $lead) {
          $user = $em->getRepository(User::class)->find(3231);

          if ($user === null) {
              throw new BadRequestHttpException('Please provide a valid user id');
          }

          $leadEntity = new Lead($user);
          $leadEntity
              ->setEmail($lead['email'])
              ->setName($lead['name'])
              ->setCreatedAt(new \DateTime('now'));
          $em->persist($leadEntity);
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
              'email' => $data[0],
              'name' => $data[1]
            ];
          }

          fclose($handle);
        }

        return $exercises;
    }

}
