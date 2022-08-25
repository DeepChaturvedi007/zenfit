<?php declare(strict_types=1);

namespace AppBundle\Fixture;

use AppBundle\Entity\ExerciseType;
use AppBundle\Repository\ExerciseTypeRepository;

class ExerciseTypeFixturesLoader
{
    public function __construct(private ExerciseTypeRepository $exerciseTypeRepository)
    {
    }

    public function __invoke(): void
    {
        foreach ($this->getData() as $item) {
            $object = $this->exerciseTypeRepository->findOneBy(['name' => $item]);
            if ($object !== null) {
                continue;
            }

            $object = new ExerciseType($item);

            $this->exerciseTypeRepository->persist($object);
        }

        $this->exerciseTypeRepository->flush();
    }

    private function getData(): array
    {
        return
            [
                'Strength',
                'Stretch',
                'Stretching',
                'Cardio',
            ];
    }
}
