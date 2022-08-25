<?php

namespace AppBundle\Command\Utils;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use AppBundle\Command\CommandBase;
use AppBundle\Services\WorkoutPlanService;
use AppBundle\Repository\WorkoutPlanRepository;

class BulkAssignWorkoutTemplateCommand extends CommandBase
{
    private EntityManagerInterface $em;
    private WorkoutPlanService $workoutPlanService;
    private WorkoutPlanRepository $workoutPlanRepository;

    public function __construct(
        EntityManagerInterface $em,
        WorkoutPlanService $workoutPlanService,
        WorkoutPlanRepository $workoutPlanRepository
    ) {
        $this->em = $em;
        $this->workoutPlanService = $workoutPlanService;
        $this->workoutPlanRepository = $workoutPlanRepository;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('zf:bulk:assign:workout:template')
            ->setDescription('Bulk assign workout template.')
            ->addArgument('clients', InputArgument::REQUIRED, 'clients')
            ->addArgument('template', InputArgument::REQUIRED, 'template');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $clients = [];
        if (is_string($input->getArgument('clients'))) {
            $clients = (array) explode(',', $input->getArgument('clients'));
        }

        $template = $this
            ->workoutPlanRepository
            ->find($input->getArgument('template'));

        if (!$template) {
            throw new BadRequestHttpException('Provide correct template');
        }

        $this
            ->workoutPlanService
            ->assignPlanToClients($template, $clients);

        return 0;
    }

}
