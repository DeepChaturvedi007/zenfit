<?php

namespace AppBundle\Command\Meal;

use AppBundle\Entity\Recipe;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use AppBundle\Command\CommandBase;
use AppBundle\Entity\RecipeType;

class CreateRecipeTypesCommand extends CommandBase
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('zf:create:recipe:types')
            ->setDescription('Create recipe types');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $em = $this->em;
        $recipes = $em->getRepository(Recipe::class)->findBy([
          'deleted' => false,
          'approved' => true
        ]);

        foreach($recipes as $recipe) {
          /*$recipeType = $em->getRepository('AppBundle:RecipeType')->findOneBy([
            'recipe' => $recipe->getId(),
            'type' => $recipe->getType(),
          ]);

          if(!$recipeType) {*/
            if($recipe->getType() == 4) {
              //insert for all 3 types of snack
              $recipeType = new RecipeType($recipe, 4);
              $em->persist($recipeType);
              $recipeType = new RecipeType($recipe, 5);
              $em->persist($recipeType);
              $recipeType = new RecipeType($recipe, 6);
              $em->persist($recipeType);
            } else {
              $recipeType = new RecipeType($recipe, $recipe->getType());
              $em->persist($recipeType);
            }
            $em->flush();
        //  }
        }

        $em->flush();

        return 0;
    }
}
