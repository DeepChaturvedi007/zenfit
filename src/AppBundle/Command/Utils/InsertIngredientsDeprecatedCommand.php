<?php

namespace AppBundle\Command\Utils;

use AppBundle\Entity\Language;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use AppBundle\Command\CommandBase;
use AppBundle\Entity\MealProduct;
use AppBundle\Entity\MealProductLanguage;
use AppBundle\Entity\MealProductWeight;

class InsertIngredientsDeprecatedCommand extends CommandBase
{
    private EntityManagerInterface $em;
    private string $projectRoot;

    public function __construct(EntityManagerInterface $em, string $projectRoot)
    {
        $this->em = $em;
        $this->projectRoot = $projectRoot;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('zf:insert:ingredients:deprecated')
            ->setDescription('Insert ingredients');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $em = $this->em;
        $file = $this->projectRoot . '/no.csv';
        $products = $this->parseCSV($file);

        foreach ($products as $product) {
            $mp = new MealProduct($product['name']);
            $mp
                ->setBrand($product['brand'])
                ->setKcal((int) $this->convertCommaToDot($product['kcal']))
                ->setProtein((float) $this->convertCommaToDot((float) $product['protein']))
                ->setFat((float) $this->convertCommaToDot($product['fat']))
                ->setSaturatedFats((float) $this->convertCommaToDot($product['saturated']))
                ->setCarbohydrates((float) $this->convertCommaToDot($product['carbohydrates']))
                ->setAddedSugars((float) $this->convertCommaToDot($product['sugar']))
                ->setFiber((float) $this->convertCommaToDot($product['fibers']))
                ->setLabel($product['label']);

            $em->persist($mp);

            $lang = $em->getRepository(Language::class)->find(4);
            if ($lang === null) {
                throw new \RuntimeException('No lang 4 in DB');
            }
            $mpl = new MealProductLanguage($product['name'], $lang, $mp);
            $em->persist($mpl);

            if ($product['amountSize'] && $product['amountSize'] != "") {
                $mpw = new MealProductWeight();
                $mpw
                    ->setName($product['amountName'])
                    ->setWeight($product['amountSize'])
                    ->setProduct($mp)
                    ->setLocale('nb_NO');

                $em->persist($mpw);
            }
        }

        $em->flush();

        return 0;
    }

    public function convertCommaToDot(mixed $value): ?float
    {
        if (is_string($value)) {
            return (float) str_replace(",", ".", $value);
        }

        return is_numeric($value) ? (float) $value : null;
    }

    private function parseCSV($file)
    {
        $products = [];
        $row = 1;
        if (($handle = fopen($file, "r")) !== FALSE) {
          while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $row++;
            if($row < 4) continue;
            $products[] = [
              'name' => ucwords(strtolower($data[0])),
              'brand' => $data[1],
              'kcal' => $data[2],
              'amountSize' => $data[3],
              'amountName' => $data[4],
              'protein' => $data[5],
              'fat' => $data[6],
              'saturated' => $data[7],
              'carbohydrates' => $data[8],
              'sugar' => $data[9],
              'salt' => $data[10],
              'fibers' => $data[11],
              'label' => $data[12],
            ];
          }

          fclose($handle);
        }

        return $products;
    }

}
