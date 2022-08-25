<?php

namespace AdminBundle\Controller;

use AppBundle\Entity\Subscription;
use AppBundle\Enums\CookingTime;
use AppBundle\Enums\MacroSplit;
use AppBundle\Enums\MealType;
use AppBundle\Enums\Language;
use AppBundle\Entity\Recipe;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends AbstractController
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function indexAction()
    {
        return $this->render('@Admin/default/index.html.twig');
    }

    public function recipesAction()
    {
        $typeOptions        = MealType::readables();
        $durationOptions    = CookingTime::readables();
        $macroOptions       = MacroSplit::readables();
        $languageOptions    = Language::readables();

        return $this->render('@Admin/default/recipes/index.html.twig', [
            'typeOptions' => $typeOptions,
            'macroOptions' => $macroOptions,
            'durationOptions' => $durationOptions,
            'languageOptions' => $languageOptions,
        ]);
    }

    public function recipeChildrenAction(Recipe $recipe)
    {
        $titlesAndDescriptions = $recipe->getAllRecipeTitlesAndDescriptions();

        return $this->render('@Admin/default/recipes/recipe_children.html.twig', [
            'recipe' => $recipe,
            'titlesAndDescriptions' => $titlesAndDescriptions->toArray()
        ]);
    }

    public function usersAction()
    {
        $users = [];

        return $this->render('@Admin/default/users.html.twig', compact('users'));
    }

    public function clientsAction(): Response
    {
        return $this->render('@Admin/default/clients.html.twig');
    }

    public function generateCustomerAction()
    {
        $subscriptions = $this
            ->em
            ->getRepository(Subscription::class)
            ->findAll();

        return $this->render('@Admin/default/generate-customer.html.twig', compact('subscriptions'));
    }

    public function ingredientsAction()
    {
        $ingredients = true;
        return $this->render('@Admin/default/ingredients.html.twig', compact('ingredients'));
    }

    public function growthTrainersAction()
    {
        return $this->render('@Admin/default/growth-trainers.html.twig');
    }
}
