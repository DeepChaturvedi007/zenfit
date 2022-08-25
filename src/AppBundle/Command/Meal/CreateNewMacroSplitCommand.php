<?php

namespace AppBundle\Command\Meal;

use Doctrine\ORM\EntityManagerInterface;
use MealBundle\Services\RecipeBaseService;
use MealBundle\Services\RecipeCustomGeneratorService;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use AppBundle\Entity\MealPlan;
use AppBundle\Command\CommandBase;
use AppBundle\Entity\Recipe;

class CreateNewMacroSplitCommand extends CommandBase
{
    private RecipeBaseService $recipeBaseService;
    private EntityManagerInterface $em;
    private RecipeCustomGeneratorService $recipeCustomGeneratorService;

    public function __construct(
        RecipeBaseService $recipeBaseService,
        RecipeCustomGeneratorService $recipeCustomGeneratorService,
        EntityManagerInterface $em
    ) {
        $this->recipeBaseService = $recipeBaseService;
        $this->recipeCustomGeneratorService = $recipeCustomGeneratorService;
        $this->em = $em;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('zf:create:new:macro:splits')
            ->setDescription('Create new macro splits for recipes')
            ->addArgument('recipe', InputArgument::OPTIONAL, 'Recipe id');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $em = $this->em;

        //config
        $targetMs = 2; //the macro split we wish to create the new recipe in
        $ms = 6; // macroSplit of recipes we want to clone
        $locale = 'da_DK'; // locale of recipes we want to clone
        //end config

        $recipes = [];
        if($input->getArgument('recipe')) {
          $recipes[] = $em->getRepository(Recipe::class)->find($input->getArgument('recipe'));
        } else {
          $recipes = $em->getRepository(Recipe::class)->getRecipesCreatedByZenfitByMacroSplitAndLocale($ms, $locale);
        }

        $successfulRecipes = [];
        $unSuccessfulRecipes = [];

        foreach($recipes as $recipe) {
          $newRecipe = clone $recipe;
          $newRecipe
            ->setMacroSplit($targetMs)
            ->setApproved(false);

          $em->persist($newRecipe);
          $em->flush();

          try {
              $this->cloneRecipeProducts($recipe, $newRecipe);
              $em->flush();
              $em->refresh($newRecipe);
              $this->adjustIngredientWeights($newRecipe, MealPlan::MACRO_SPLIT[$targetMs]);

              $this->cloneRecipeMeta($recipe, $newRecipe);
              $this->cloneRecipeTypes($recipe, $newRecipe);
              $successfulRecipes[] = $newRecipe->getId();
          } catch(\Exception $e) {
              var_dump($e->getMessage());
              $unSuccessfulRecipes[] = $newRecipe->getId();
          }

          $em->flush();
        }

        $output->writeln([
          "<info>Successful recipes</info>",
            var_export($successfulRecipes, true)
        ]);

        $output->writeln([
          "<comment>Unsuccessful recipes</comment>",
          var_export($unSuccessfulRecipes, true)
        ]);

        $success = array (
            array('recipes'),
            $successfulRecipes
        );

        $nosuccess = array (
            array('recipes'),
            $unSuccessfulRecipes
        );

        $this->outputCSV($success, 'success.csv');
        $this->outputCSV($nosuccess, 'nosuccess.csv');

        return 0;
    }

    private function cloneRecipeMeta(Recipe $recipe, Recipe $newRecipe)
    {
        $recipeMeta = $recipe->getRecipeMeta();
        if ($recipeMeta === null) {
            throw new \RuntimeException('RecipeMeta is null');
        }

        $newRecipeMeta = clone $recipeMeta;
        $newRecipeMeta->setRecipe($newRecipe);
        $this->em->persist($newRecipeMeta);
    }

    private function cloneRecipeTypes(Recipe $recipe, Recipe $newRecipe)
    {
        foreach($recipe->getTypes() as $recipeType) {
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

    private function adjustIngredientWeights(Recipe $newRecipe, $macroSplit)
    {
        //get total kcals in recipe
        $totalKcals = $newRecipe->getKcals();

        //create array with desired number of protein, carbs, and fat
        $macros = [
          'carbohydrate' => round($macroSplit['carbohydrate'] * $totalKcals / 4),
          'protein' => round($macroSplit['protein'] * $totalKcals / 4),
          'fat' => round($macroSplit['fat'] * $totalKcals / 9)
        ];

        $currentRecipes = [];
        $attempt = 0;

        $result = $this
            ->recipeCustomGeneratorService
            ->attemptToHitMacros($newRecipe, $macros, $newRecipe->getType(), $currentRecipes, $attempt, false);
        $ratio = $result['ratios'];

        $recipeBaseService = $this->recipeBaseService;

        //loop through all ingredients in the meal and apply ratio
        foreach($newRecipe->getProducts() as $product) {
            $ingRatio = $recipeBaseService->getRatio($ratio, $product);
            $recipeBaseService->adjustFoodProductWeight(null, (int) $ingRatio, $product);
        }
    }

    function outputCSV($data, $name)
    {
        $fp = fopen($name, 'wb');
        if ($fp === false) {
            throw new \RuntimeException();
        }
        foreach ($data as $item) {
            fputcsv($fp, $item);
        }
        fclose($fp);
    }


}
