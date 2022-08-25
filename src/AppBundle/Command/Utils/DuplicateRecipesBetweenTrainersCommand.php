<?php

namespace AppBundle\Command\Utils;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use AppBundle\Command\CommandBase;
use AppBundle\Entity\Recipe;
use AppBundle\Repository\UserRepository;
use AppBundle\Repository\RecipeRepository;

class DuplicateRecipesBetweenTrainersCommand extends CommandBase
{
    private EntityManagerInterface $em;
    private UserRepository $userRepository;
    private RecipeRepository $recipeRepository;

    public function __construct(
        EntityManagerInterface $em,
        UserRepository $userRepository,
        RecipeRepository $recipeRepository
    ) {
        $this->em = $em;
        $this->userRepository = $userRepository;
        $this->recipeRepository = $recipeRepository;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('zf:duplicate:recipes:between:trainers:command')
            ->setDescription('Duplicate recipes between trainers.')
            ->addArgument('recipes', InputArgument::REQUIRED, 'Recipes')
            ->addArgument('to', InputArgument::REQUIRED, 'To');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $toUser = $this
            ->userRepository
            ->find($input->getArgument('to'));

        if ($toUser === null) {
            throw new \RuntimeException('toUser not found');
        }

        $recipes = (array) explode(',', (string) $input->getArgument('recipes'));

        foreach ($recipes as $r) {
            $recipe = $this
                ->recipeRepository
                ->find($r);

            if ($recipe === null) {
                continue;
            }

            $newRecipe = clone $recipe;
            $newRecipe->setUser($toUser);
            $this->em->persist($newRecipe);

            $this->cloneRecipeProducts($recipe, $newRecipe);
            $this->cloneRecipeMeta($recipe, $newRecipe);
            $this->cloneRecipeTypes($recipe, $newRecipe);
        }

        $this->em->flush();

        return 0;
    }

    private function cloneRecipeMeta(Recipe $recipe, Recipe $newRecipe): void
    {
        $recipeMeta = $recipe->getRecipeMeta();
        if ($recipeMeta) {
            $newRecipeMeta = clone $recipeMeta;
            $newRecipeMeta->setRecipe($newRecipe);
            $this->em->persist($newRecipeMeta);
        }

    }

    private function cloneRecipeTypes(Recipe $recipe, Recipe $newRecipe): void
    {
        $recipeTypes = $recipe->getTypes();
        foreach($recipeTypes as $recipeType) {
          $newType = clone $recipeType;
          $newType->setRecipe($newRecipe);
          $this->em->persist($newType);
        }
    }

    private function cloneRecipeProducts(Recipe $recipe, Recipe $newRecipe): void
    {
        $em = $this->em;
        foreach($recipe->getProducts() as $product) {
          $newProduct = clone $product;
          $newProduct->setRecipe($newRecipe);
          $em->persist($newProduct);
        }
    }

}
