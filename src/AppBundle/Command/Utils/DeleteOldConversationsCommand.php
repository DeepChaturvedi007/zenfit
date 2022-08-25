<?php

namespace AppBundle\Command\Utils;

use ChatBundle\Entity\Conversation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use AppBundle\Command\CommandBase;

class DeleteOldConversationsCommand extends CommandBase
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
            ->setName('zf:remove:old:conversations')
            ->setDescription('Remove old conversations');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $em = $this->em;
        $file = $this->projectRoot . '/justin.csv';
        $conversations = $this->parseCSV($file);

        foreach($conversations as $conversation) {
            $entity = $em->getRepository(Conversation::class)->find($conversation['conv_id']);
            if ($entity !== null) {
                $entity->setDeleted(true);
            }
        }

        $em->flush();

        return 0;
    }

    private function parseCSV($file)
    {
        $products = [];
        $row = 1;
        if (($handle = fopen($file, "r")) !== FALSE) {
          while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $row++;
            if($row < 3) continue;
            $products[] = [
              'conv_id' => $data[1]
            ];
          }

          fclose($handle);
        }

        return $products;
    }

}
