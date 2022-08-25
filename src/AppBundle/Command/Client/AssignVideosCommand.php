<?php declare(strict_types=1);

namespace AppBundle\Command\Client;

use AppBundle\Command\CommandBase;
use AppBundle\Entity\Video;
use AppBundle\Repository\VideoRepository;
use DoctrineBatchUtils\BatchProcessing\SimpleBatchIteratorAggregate;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use VideoBundle\Services\VideoService;

class AssignVideosCommand extends CommandBase
{
    private VideoRepository $videoRepository;
    private VideoService $videoService;

    public function __construct(
        VideoService $videoService,
        VideoRepository $videoRepository
    ) {
        $this->videoRepository = $videoRepository;
        $this->videoService = $videoService;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('zf:client:videos:assign')
            ->setDescription('Assign videos to clients according to days after activation and tags')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $query = $this->videoRepository
            ->createQueryBuilder('v')
            ->andWhere('v.assignWhen > 0')
            ->andWhere('v.deleted = 0')
            ->getQuery();

        $iterator = SimpleBatchIteratorAggregate::fromQuery($query, 100);

        /** @var Video $video */
        foreach ($iterator as $video) {
            $this->videoService->assignVideoToClients($video, true);
        }

        return 0;
    }
}
