<?php

namespace AppBundle\Command\Utils;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use AppBundle\Command\CommandBase;
use AppBundle\Entity\Recipe;

class InsertRecipesCommand extends CommandBase
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
            ->setName('zf:insert:recipes')
            ->setDescription('Insert recipes');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $em = $this->em;
        $file = $this->projectDir . '/de.csv';
        $recipes = $this->parseCSV($file);

        $i = 0;

        foreach($recipes as $recipe) {

            $r = $em->getRepository(Recipe::class)->find($recipe['id']);
            if ($r === null) {
                continue;
            }

            if($recipe['title'] == '#N/A') continue;
            $recipeParent = $r->getParent();
            if($recipeParent) {
                $r = $recipeParent;
            } else {
                continue;
            }

            $newRecipe = (clone $r)
                ->setName($recipe['title'])
                ->setLocale('de_DE')
                ->setComment($recipe['desc'])
                ->setApproved(true)
                ->setParent($r);

            $em->persist($newRecipe);
            $this->cloneRecipeMeta($r, $newRecipe);
            $this->cloneRecipeTypes($r, $newRecipe);
            $this->cloneRecipeProducts($r, $newRecipe);

            $i++;
            var_dump($i);
        }

        $em->flush();

        return 0;
    }

    private function cloneRecipeMeta(Recipe $recipe, Recipe $newRecipe)
    {
        $recipeMeta = $recipe->getRecipeMeta();
        if ($recipeMeta === null) {
            throw new \RuntimeException('Recipe meta is null');
        }

        $newRecipeMeta = clone $recipeMeta;
        $newRecipeMeta->setRecipe($newRecipe);
        $this->em->persist($newRecipeMeta);
    }

    private function cloneRecipeTypes(Recipe $recipe, Recipe $newRecipe)
    {
        $recipeTypes = $recipe->getTypes();
        foreach($recipeTypes as $recipeType) {
          $newType = clone $recipeType;
          $newType->setRecipe($newRecipe);
          $this->em->persist($newType);
        }
    }

    private function cloneRecipeProducts(Recipe $recipe, Recipe $newRecipe)
    {
        foreach($recipe->getProducts() as $product) {
          $newProduct = clone $product;
          $newProduct->setRecipe($newRecipe);
          $this->em->persist($newProduct);
        }
    }

    private function parseCSV($file)
    {
        $recipes = [];
        $row = 1;
        if (($handle = fopen($file, "r")) !== FALSE) {
          while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $row++;
            if($row < 3) continue;
            $recipes[] = [
              'id' => $data[0],
              'title' => $data[3],
              'desc' => $data[4],
            ];
          }

          fclose($handle);
        }

        return $recipes;
    }

}
