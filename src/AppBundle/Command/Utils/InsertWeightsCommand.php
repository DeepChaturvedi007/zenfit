<?php

namespace AppBundle\Command\Utils;

use AppBundle\Entity\MealProductWeight;
use AppBundle\Entity\Recipe;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use AppBundle\Command\CommandBase;

class InsertWeightsCommand extends CommandBase
{
    private EntityManagerInterface $em;
    private string $projectDir;

    public function __construct(EntityManagerInterface $em, string $projectDir)
    {
        $this->em = $em;
        $this->projectDir = $projectDir;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('zf:insert:weights')
            ->setDescription('Insert weights');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $em = $this->em;
        $file = $this->projectDir . '/weights.csv';
        $weights = $this->parseCSV($file);
        $locale = 'de_DE';

        $recipes = $em->getRepository(Recipe::class)->findBy([
            'locale' => $locale,
            'user' => null
        ]);

        foreach($recipes as $recipe) {
            foreach($recipe->getProducts() as $product) {
                $productWeight = $product->getWeight();
                if ($productWeight === null) {
                    continue;
                }

                $weightId = $productWeight->getId();

                try {
                    if($weightId && isset($weights[$weightId])) {

                        $weightName = $weights[$weightId]['name'];
                        $weightEntity = $em->getRepository(MealProductWeight::class)->findOneBy([
                            'locale' => $locale,
                            'product' => $product->getProduct(),
                            'name' => $weightName
                        ]);

                        if(!$weightEntity) {
                            $weightEntity = (clone $productWeight)
                                ->setLocale($locale)
                                ->setName($weightName);
                            $em->persist($weightEntity);
                            $em->flush();
                        }

                        $product->setWeight($weightEntity);
                    }
                } catch (\Exception $e) {
                    var_dump($e->getMessage());
                } catch (DBALException $e) {
                    var_dump($e->getMessage());
                }
            }
        }

        $em->flush();

        return 0;
    }

    private function parseCSV($file)
    {
        $recipes = [];
        $row = 1;
        if (($handle = fopen($file, "r")) !== FALSE) {
          while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $row++;
            if($row < 3) continue;
            $recipes[trim($data[0])] = [
              'id' => trim($data[0]),
              'name' => trim($data[3]),
            ];
          }

          fclose($handle);
        }

        return $recipes;
    }

}
