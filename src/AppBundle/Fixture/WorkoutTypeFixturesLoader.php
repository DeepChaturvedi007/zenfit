<?php declare(strict_types=1);

namespace AppBundle\Fixture;

use AppBundle\Entity\WorkoutType;
use AppBundle\Repository\WorkoutTypeRepository;

class WorkoutTypeFixturesLoader
{
    private WorkoutTypeRepository $workoutTypeRepository;

    public function __construct(WorkoutTypeRepository $workoutTypeRepository)
    {
        $this->workoutTypeRepository = $workoutTypeRepository;
    }

    public function __invoke(): void
    {
        foreach ($this->getData() as $item) {
            $object = $this->workoutTypeRepository->findOneBy(['name' => $item]);
            if ($object !== null) {
                continue;
            }

            $object = new WorkoutType();
            $object->setName($item);

            $this->workoutTypeRepository->persist($object);
        }

        $this->workoutTypeRepository->flush();
    }

    private function getData(): array
    {
        return
            [
                'Reps',
                'Time',
            ];
    }
}
