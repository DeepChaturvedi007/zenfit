<?php declare(strict_types=1);

namespace AppBundle\Fixture;

use AppBundle\Entity\MuscleGroup;
use AppBundle\Repository\MuscleGroupRepository;

class MuscleGroupsFixturesLoader
{
    public function __construct(private MuscleGroupRepository $muscleGroupRepository)
    {
    }

    public function __invoke(): void
    {
        foreach ($this->getData() as $item) {
            $object = $this->muscleGroupRepository->findOneBy(['name' => $item]);
            if ($object !== null) {
                continue;
            }

            $object = new MuscleGroup();
            $object->setName($item);

            $this->muscleGroupRepository->persist($object);
        }

        $this->muscleGroupRepository->flush();
    }

    private function getData(): array
    {
        return
            [
                'Chest',
                'Shoulders',
                'Biceps',
                'Triceps',
                'Lats',
                'Calves',
                'Glutes',
                'Hamstring',
                'Quadriceps',
                'Middle Back',
                'Traps',
                'Neck',
                'Lower Back',
                'Abdominals',
                'Abductors',
                '-+',
            ];
    }
}
