<?php declare(strict_types=1);

namespace AppBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use AppBundle\Consumer\VideoCompressEvent;
use AppBundle\Consumer\VoiceCompressEvent;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class FixVideosCommand extends CommandBase
{
    private EntityManagerInterface $em;
    private MessageBusInterface $messageBus;

    public function __construct(EntityManagerInterface $em, MessageBusInterface $messageBus)
    {
        $this->em = $em;
        $this->messageBus = $messageBus;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('zf:fix:videos');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $ids = [];
        $media = '';
        $pathinfo = pathinfo($media);
        if (array_key_exists('extension', $pathinfo) && $pathinfo['extension'] === 'wav') {
            $this->messageBus->dispatch(new VoiceCompressEvent($ids, true, $media));
        } else {
            $this->messageBus->dispatch(new VideoCompressEvent($ids, true, $media));
        }

        return 0;
    }

}
