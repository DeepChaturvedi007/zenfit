<?php

namespace AppBundle\Command\Workout;

use AppBundle\Entity\WorkoutPlanSettings;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use AppBundle\Command\CommandBase;

class EnableWorkoutSettingsTimeCommand extends CommandBase
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
            ->setName('zf:workout:enable-time')
            ->setDescription('Enable workout plan settings time for all plans');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $em = $this->em;
        $repo = $em->getRepository(WorkoutPlanSettings::class);
        $repo
            ->createQueryBuilder('wps')
            ->update()
            ->set('wps.time', ':timeType')
            ->setParameter('timeType', true)
            ->where('wps.time = 0')
            ->getQuery()
            ->execute();

        return 0;
    }
}
