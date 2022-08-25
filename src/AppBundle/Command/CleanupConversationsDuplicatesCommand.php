<?php declare(strict_types=1);

namespace AppBundle\Command;

use ChatBundle\Entity\Conversation;
use ChatBundle\Entity\Message;
use Doctrine\DBAL\Cache\ResultCacheStatement;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CleanupConversationsDuplicatesCommand extends CommandBase
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('zf:fix:conv-duplicates');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var ResultCacheStatement<mixed> $statement */
        $statement = $this->em->getConnection()->executeQuery('select max(id) duplicated_id_max, min(id) duplicated_id_min, user_id,client_id, count(id) count from conversations  GROUP BY user_id, client_id having count(id)>1');
        $result = $statement->fetchAllAssociative();

        foreach ($result as $item) {
            if ($item['count'] > 2) {
                dump($item);
                throw new \Exception('3 conversations duplicates');
            }
            $duplicatedIdMax = $item['duplicated_id_max'];
            $duplicatedIdMin = $item['duplicated_id_min'];
            /** @var Conversation $conversationMax */
            $conversationMax = $this->em->find(Conversation::class, $duplicatedIdMax);
            /** @var Conversation $conversationMin */
            $conversationMin = $this->em->find(Conversation::class, $duplicatedIdMin);

            if ($conversationMax->getMessages()->count() !== 0) {
                /** @var Message $message */
                foreach ($conversationMax->getMessages() as $message) {
                    dump('update conv from '.$message->getConversation()->getId().' to '.$conversationMin->getId());
                    $message->setConversation($conversationMin);
                }
            }
            dump('remove '.$item['duplicated_id_max']);
            $this->em->remove($conversationMax);
            $this->em->flush();
        }

        return 0;
    }

}
