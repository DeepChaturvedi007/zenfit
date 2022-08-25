<?php

namespace AppBundle\Command\Utils;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use AppBundle\Command\CommandBase;
use AppBundle\Entity\Recipe;
use AppBundle\Services\RecipesService;
use AppBundle\Repository\RecipeRepository;

class SyncRecipeMetaCommand extends CommandBase
{
    public function __construct(
        private EntityManagerInterface $em,
        private RecipesService $recipesService,
        private RecipeRepository $recipeRepository
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('zf:sync:recipe:meta')
            ->setDescription('Sync recipe meta');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $recipes = $this
            ->recipeRepository
            ->findBy([
                'user' => null,
                'deleted' => 0,
                'approved' => 1
            ]);

        foreach ($recipes as $recipe) {
            $recipeMeta = $recipe->getRecipeMeta();
            foreach ($recipe->getProducts() as $product) {
                if ($product->getProduct()->getMealProductMeta() && $recipeMeta) {
                    $this->recipesService->updateRecipeMeta($recipeMeta, $product->getProduct());
                }
            }
        }

        $this->em->flush();

        return 0;
    }
}
