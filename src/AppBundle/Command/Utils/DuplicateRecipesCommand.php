<?php

namespace AppBundle\Command\Utils;

use AppBundle\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use AppBundle\Command\CommandBase;
use AppBundle\Entity\Recipe;

class DuplicateRecipesCommand extends CommandBase
{
    public function __construct(private EntityManagerInterface $em)
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('zf:duplicate:recipes:command')
            ->setDescription('Duplicate recipes command');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $user = $this
            ->em
            ->getRepository(User::class)
            ->find(3224);

        if ($user === null) {
            throw new \RuntimeException('User not found');
        }

        $recipes = $this
            ->em
            ->getRepository(Recipe::class)
            ->findBy([
                'user' => $user,
                'deleted' => 0,
                'approved' => 1
            ]);

        foreach ($recipes as $recipe) {
            $newRecipe = clone $recipe;
            $newRecipe
              ->setApproved(false)
              ->setUser(null);

            $this->em->persist($newRecipe);

            $this->cloneRecipeProducts($recipe, $newRecipe);
            $this->cloneRecipeMeta($recipe, $newRecipe);
            $this->cloneRecipeTypes($recipe, $newRecipe);
            $this->em->flush();
        }

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
        $em = $this->em;
        foreach($recipe->getProducts() as $product) {
          $newProduct = clone $product;
          $newProduct->setRecipe($newRecipe);
          $em->persist($newProduct);
        }
    }

}
