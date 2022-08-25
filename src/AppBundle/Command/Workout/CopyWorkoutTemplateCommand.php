<?php

namespace AppBundle\Command\Workout;

use AppBundle\Entity\User;
use AppBundle\Entity\WorkoutPlan;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use AppBundle\Command\CommandBase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CopyWorkoutTemplateCommand extends CommandBase
{
    public function __construct(private EntityManagerInterface $em)
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('zf:workout:templates:copy')
            ->setDescription('Copy Workout Templates')
            ->addArgument('from', InputArgument::REQUIRED, 'From')
            ->addArgument('to', InputArgument::REQUIRED, 'To');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $em = $this->em;
        $copyFrom = $em->getRepository(User::class)->find($input->getArgument('from'));
        $copyTo = $em->getRepository(User::class)->find($input->getArgument('to'));
        if ($copyTo === null) {
            throw new \RuntimeException('No CopyTo user found');
        }

        /** @var WorkoutPlan[] $templates */
        $templates = $em->getRepository(WorkoutPlan::class)->findBy([
          'user' => $copyFrom,
          'deleted' => false,
          'template' => 1
        ]);

        foreach($templates as $template) {
          $newTemplate = clone $template;
          $em->persist($newTemplate);
          $newTemplate->setUser($copyTo);

          $settings = $template->getSettings();
          if ($settings !== null) {
              $newSettings = clone $settings;
              $newSettings->setPlan($newTemplate);
              $em->persist($newSettings);
          }

          foreach($template->getWorkoutDays() as $day) {
            $newWorkoutDay = clone $day;
            $em->persist($newWorkoutDay);
            $newWorkoutDay->setWorkoutPlan($newTemplate);

            foreach($day->getWorkouts() as $workout) {
              $newWorkout = clone $workout;
              $em->persist($newWorkout);
              $workout->setWorkoutDay($newWorkoutDay);
            }
          }
        }

        $em->flush();

        return 0;
    }
}
