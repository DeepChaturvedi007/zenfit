<?php declare(strict_types=1);

namespace AppBundle\Fixture;

use AppBundle\Entity\Equipment;
use AppBundle\Repository\EquipmentRepository;

class EquipmentsFixturesLoader
{
    public function __construct(private EquipmentRepository $equipmentRepository)
    {
    }

    public function __invoke(): void
    {
        foreach ($this->getData() as $item) {
            $equipment = $this->equipmentRepository->findOneBy(['name' => $item]);
            if ($equipment !== null) {
                continue;
            }

            $equipment = new Equipment();
            $equipment->setName($item);

            $this->equipmentRepository->persist($equipment);
        }

        $this->equipmentRepository->flush();
    }

    private function getData(): array
    {
        return
            [
                'Barbell',
                'Dumbbell',
                'Machine',
                'Bands',
                'Bodyweight',
                'Cable',
                'Kettlebelt',
                'Body Weight',
                'Exercise ball',
                'Other',
                'Ingen',
                'E-Z Curl Bar',
                'Body Only',
                'Foam Roll',
                'None',
                'Exercise Ball',
                'Kropsvægt',
                'Kettlebell',
                'Medicine Ball',
            ];
    }
}
