<?php declare(strict_types=1);

namespace AppBundle\Fixture;

use AppBundle\Entity\WorkoutPlan;
use AppBundle\Repository\WorkoutPlanRepository;

class WorkoutPlanFixturesLoader
{
    private WorkoutPlanRepository $workoutPlanRepository;

    public function __construct(WorkoutPlanRepository $workoutPlanRepository)
    {
        $this->workoutPlanRepository = $workoutPlanRepository;
    }

    public function __invoke(): void
    {
        foreach ($this->getData() as $item) {
            $object = $this->workoutPlanRepository->findOneBy(['name' => $item[0]]);
            if ($object !== null) {
                continue;
            }

            $object = new WorkoutPlan($item[0]);
            $object->setStatus($item[1]);
            $object->setComment($item[2]);
            $object->setTemplate($item[3]);

            $this->workoutPlanRepository->persist($object);
        }

        $this->workoutPlanRepository->flush();
    }

    /** @return array<mixed> */
    private function getData(): array
    {
        return
            [
                ['Test template', 'active',	'test comment',	true]
            ];
    }
}
