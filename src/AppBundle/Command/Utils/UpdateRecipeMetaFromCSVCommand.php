<?php

namespace AppBundle\Command\Utils;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use AppBundle\Command\CommandBase;
use AppBundle\Entity\MealProductMeta;
use AppBundle\Entity\MealProduct;

class UpdateRecipeMetaFromCSVCommand extends CommandBase
{
    private EntityManagerInterface $em;
    private string $projectDir;

    public function __construct(EntityManagerInterface $em, string $projectDir)
    {
        $this->em = $em;
        $this->projectDir = $projectDir;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('zf:update:recipe:meta:from:csv')
            ->setDescription('Insert exercise from csv')
            ->addArgument('file', InputArgument::REQUIRED, 'File');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $em = $this->em;
        $file = $this->projectDir . '/' . $input->getArgument('file');

        $ingredients = $this->parseCSV($file);
        foreach ($ingredients as $ingredient) {
            $id = $ingredient['id'];
            $deleted = $ingredient['deleted'];

            $mealProduct = $em
                ->getRepository(MealProduct::class)
                ->find($id);

            if (!$mealProduct) {
                continue;
            }

            $mealProductMeta = $em
                ->getRepository(MealProductMeta::class)
                ->findOneBy([
                    'mealProduct' => $mealProduct
                ]);

            if (!$mealProductMeta) {
                $mealProductMeta = new MealProductMeta($mealProduct);
                $this->em->persist($mealProductMeta);
            }

            $mealProductMeta
                ->setLactose($ingredient['containsLactose'])
                ->setGluten($ingredient['containsGluten'])
                ->setNuts($ingredient['containsNuts'])
                ->setEggs($ingredient['containsEggs'])
                ->setPig($ingredient['containsPig'])
                ->setShellfish($ingredient['containsShellfish'])
                ->setFish($ingredient['containsFish'])
                ->setNotVegetarian($ingredient['notVegetarian'])
                ->setNotVegan($ingredient['notVegan'])
                ->setNotPescetarian($ingredient['notPescetarian']);

            $mealProduct->setDeleted($deleted);
            $this->em->flush();
        }

        return 0;
    }

    /** @return array<int, mixed> */
    private function parseCSV(string $file): array
    {
        $ingredients = [];
        $row = 1;
        if (($handle = fopen($file, "r")) !== FALSE) {
          while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $row++;

            if($row < 3) continue;
            $ingredients[] = [
              'id' => isset($data[0]) ? (int)$data[0]: null,
              'containsLactose' => isset($data[2]) ? (bool)$data[2]: null,
              'containsGluten' => isset($data[3]) ? (bool)$data[3]: null,
              'containsNuts' => isset($data[4]) ? (bool)$data[4]: null,
              'containsEggs' => isset($data[5]) ? (bool)$data[5]: null,
              'containsPig' => isset($data[6]) ? (bool)$data[6]: null,
              'containsShellfish' => isset($data[7]) ? (bool)$data[7]: null,
              'containsFish' => isset($data[8]) ? (bool)$data[8]: null,
              'notVegetarian' => isset($data[9]) ? (bool)$data[9]: null,
              'notVegan' => isset($data[10]) ? (bool)$data[10]: null,
              'notPescetarian' => isset($data[11]) ? (bool)$data[11]: null,
              'deleted' => isset($data[12]) ? (bool)$data[12]: null
            ];
          }

          fclose($handle);
        }

        return $ingredients;
    }
}
