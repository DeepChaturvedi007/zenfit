<?php

namespace WorkoutPlanBundle\Transformer;

use AppBundle\Entity\WorkoutDay;
use AppBundle\Entity\Workout;
use AppBundle\Repository\WorkoutRepository;
use Doctrine\ORM\EntityManagerInterface;
use WorkoutPlanBundle\Transformer\WorkoutTransformer;
use League\Fractal\TransformerAbstract;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface as Container;

class WorkoutDayTransformer extends TransformerAbstract
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @param WorkoutDay $day
     * boolean $last
     * @return array
     */
    public function transform(WorkoutDay $day, $last = false)
    {
        $workoutTransformer = new WorkoutTransformer();

        /** @var WorkoutRepository $workoutRepo */
        $workoutRepo = $this
            ->em
            ->getRepository(Workout::class);

        $workouts = collect($workoutRepo->getByWorkoutDayIds([$day->getId()]))
            ->groupBy(function (Workout $x) { return $x->getWorkoutDay()->getId(); });

        $data = [
            'id' => $day->getId(),
            'name' => $day->getName(),
            'comment' => $day->getWorkoutDayComment(),
    		    'order' => $day->getOrder(),
            'workouts' => [],
            'last' => $last
        ];

        $dayWorkouts = $workouts->get($day->getId());
        if ($dayWorkouts) {
            $dayWorkouts->each(function(Workout $item) use (&$data, $workoutTransformer) {
                $workout = $workoutTransformer->transform($item);
                $data['workouts'][] = $workout;
            });
        }

        return $data;
    }
}
