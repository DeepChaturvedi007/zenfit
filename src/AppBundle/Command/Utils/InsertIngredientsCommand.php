<?php

namespace AppBundle\Command\Utils;

use AppBundle\Entity\Language;
use AppBundle\Entity\MealProduct;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use AppBundle\Command\CommandBase;
use AppBundle\Entity\MealProductLanguage;

class InsertIngredientsCommand extends CommandBase
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
            ->setName('zf:insert:ingredients')
            ->setDescription('Insert ingredients');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $em = $this->em;
        $file = $this->projectDir . '/ingredients.csv';
        $products = $this->parseCSV($file);

        $lang = $em->getRepository(Language::class)->find(7);
        if ($lang === null) {
            throw new \RuntimeException('Language not found');
        }
        foreach($products as $product) {
          $mealProduct = $em
              ->getRepository(MealProduct::class)
              ->find($product['id']);

          if ($mealProduct === null) {
              throw new \RuntimeException($product['id'] . ' doesnt exist');
          }

          $mealProductLang = new MealProductLanguage($product['title'], $lang, $mealProduct);
          $em->persist($mealProductLang);
        }

        $em->flush();

        return 0;
    }

    private function parseCSV($file)
    {
        $products = [];
        $row = 1;
        if (($handle = fopen($file, "r")) !== FALSE) {
          while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $row++;
            if($row < 3) continue;
            $explodedRow = explode(',', $data[0]);
            $products[] = [
              'id' => $data[1],
              'title' => $data[3]
            ];
          }

          fclose($handle);
        }

        return $products;
    }

}
