<?php declare(strict_types=1);

namespace AppBundle\Command;

use ChatBundle\Repository\ConversationRepository;
use ChatBundle\Repository\MessageRepository;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DeleteOldClientsMessagesCommand extends CommandBase
{
    public function __construct(
        private MessageRepository $messageRepository,
        private ConversationRepository $conversationRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('zf:messages:delete-old');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $messagesToDelete = $this->messageRepository
            ->createQueryBuilder('message')
            ->select('message.id')
            ->innerJoin('message.conversation', 'conv')
            ->innerJoin('conv.client', 'client')
            ->innerJoin('conv.user', 'user')
            ->andWhere('client.deleted = 1 OR user.deleted = 1 OR message.deleted = 1')
            ->getQuery()
            ->getResult();

        $messagesToDelete = array_map(static fn (array $item) => $item['id'], $messagesToDelete);

        $this->messageRepository
            ->createQueryBuilder('m')
            ->delete()
            ->where('m.id in (:ids)')
            ->setParameter('ids', $messagesToDelete)
            ->getQuery()
            ->execute();

        $conversationsToDelete = $this->conversationRepository
            ->createQueryBuilder('conversation')
            ->select('conversation.id')
            ->innerJoin('conversation.client', 'client')
            ->innerJoin('conversation.user', 'user')
            ->andWhere('client.deleted = 1 OR user.deleted = 1')
            ->getQuery()
            ->getResult();

        $conversationsToDelete = array_map(static fn (array $item) => $item['id'], $conversationsToDelete);

        $this->conversationRepository
            ->createQueryBuilder('c')
            ->delete()
            ->where('c.id in (:ids)')
            ->setParameter('ids', $conversationsToDelete)
            ->getQuery()
            ->execute();

        return 0;
    }
}
