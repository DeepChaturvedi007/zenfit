<?php

namespace AppBundle\Command\Workout;

use AppBundle\Entity\WorkoutPlan;
use AppBundle\Entity\WorkoutPlanSettings;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use AppBundle\Command\CommandBase;

class AssignWorkoutSettingsCommand extends CommandBase
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
            ->setName('zf:workout:assign-settings')
            ->setDescription('Assign workout plan settings');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $em = $this->em;

        $repo = $em->getRepository(WorkoutPlan::class);
        $qb = $repo->createQueryBuilder('wp');

        $plans = $qb
            ->leftJoin('wp.settings', 'wps', Join::WITH, 'wps.plan = wp.id')
            ->where($qb->expr()->isNull('wps.id'))
            ->getQuery()
            ->getResult();

        $progress = new ProgressBar($output, count($plans));
        $progress->setFormatDefinition('custom', ' %current%/%max% -- %message% (%product%)');
        $progress->setFormat('custom');
        $progress->start();

        /**
         * @var $plan WorkoutPlan
         */
        foreach ($plans as $plan) {
            $settings = new WorkoutPlanSettings($plan);
            $em->persist($settings);

            $progress->setMessage('Updating workout plans...');
            $progress->setMessage($plan->getId(), 'product');
            $progress->advance();
        }

        $em->flush();

        $progress->setMessage('Updating workout plans...');
        $progress->setMessage('Done', 'product');
        $progress->advance();

        return 0;
    }
}
