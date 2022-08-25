<?php

namespace AppBundle\Command\Client\ClientStatus;

use AppBundle\Command\CommandBase;
use AppBundle\Entity\Client;
use Doctrine\ORM\EntityManagerInterface;
use DoctrineBatchUtils\BatchProcessing\SimpleBatchIteratorAggregate;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use AppBundle\Event\ClientMadeChangesEvent;
use AppBundle\Entity\Event;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class CheckForUpdatePlansCommand extends CommandBase
{
    private EntityManagerInterface $em;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(EntityManagerInterface $em, EventDispatcherInterface $eventDispatcher)
    {
        $this->em = $em;
        $this->eventDispatcher = $eventDispatcher;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('zf:check:for:update:plans')
            ->setDescription('Check to see if client needs plans updated.')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $em = $this->em;
        $clientsQuery = $em
            ->getRepository(Client::class)
            ->createQueryBuilder('c')
            ->where('c.updateWorkoutSchedule IS NOT NULL')
            ->orWhere('c.updateMealSchedule IS NOT NULL')
            ->andWhere('c.deleted = 0')
            ->andWhere('c.active = 1')
            ->andWhere('c.demoClient = 0')
            ->getQuery();

        $clients = SimpleBatchIteratorAggregate::fromQuery($clientsQuery, 100);

        /** @var Client $client */
        foreach($clients as $client) {
            echo $client->getId();
            $updateMealSchedule = $client->getUpdateMealSchedule();
            $updateWorkoutSchedule = $client->getUpdateWorkoutSchedule();

            $mealUpdated = $client->getMealUpdated();
            $workoutUpdated = $client->getWorkoutUpdated();

            if ($updateWorkoutSchedule && $workoutUpdated != null) {
                $updateWorkoutDate = $workoutUpdated->modify("+$updateWorkoutSchedule week");

                if (new \DateTime() > $updateWorkoutDate) {
                    $dispatcher = $this->eventDispatcher;
                    $event = new ClientMadeChangesEvent($client, Event::TRAINER_UPDATE_WORKOUT_PLAN);
                    $dispatcher->dispatch($event, Event::TRAINER_UPDATE_WORKOUT_PLAN);
                }
            }

            if ($updateMealSchedule && $mealUpdated != null) {
                $updateMealDate = $mealUpdated->modify("+$updateMealSchedule week");

                if (new \DateTime() > $updateMealDate) {
                    $dispatcher = $this->eventDispatcher;
                    $event = new ClientMadeChangesEvent($client, Event::TRAINER_UPDATE_MEAL_PLAN);
                    $dispatcher->dispatch($event, Event::TRAINER_UPDATE_MEAL_PLAN);
                }
            }

        }

        return 0;
    }
}
