<?php

namespace MealBundle\Controller;

use AppBundle\Controller\Controller;
use AppBundle\Entity\Language;
use AppBundle\Entity\User;
use AppBundle\Repository\LanguageRepository;
use AppBundle\Services\RecipesService;
use Doctrine\ORM\EntityManagerInterface;
use MealBundle\Transformer\RecipeTransformer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use AppBundle\Repository\MasterMealPlanRepository;
use AppBundle\Repository\RecipeRepository;

/**
 * @Route("/api/recipe")
 */
class RecipeController extends Controller
{
    private RecipesService $recipesService;
    private RecipeTransformer $recipeTransformer;
    private MasterMealPlanRepository $masterMealPlanRepository;
    private RecipeRepository $recipeRepository;
    private LanguageRepository $languageRepository;
    private EntityManagerInterface $em;

    public function __construct(
        RecipesService $recipesService,
        EntityManagerInterface $em,
        TokenStorageInterface $tokenStorage,
        RecipeTransformer $recipeTransformer,
        MasterMealPlanRepository $masterMealPlanRepository,
        RecipeRepository $recipeRepository,
        LanguageRepository $languageRepository
    ) {
        $this->recipesService = $recipesService;
        $this->recipeTransformer = $recipeTransformer;
        $this->masterMealPlanRepository = $masterMealPlanRepository;
        $this->recipeRepository = $recipeRepository;
        $this->languageRepository = $languageRepository;
        $this->em = $em;

        parent::__construct($em, $tokenStorage);
    }

    /**
     * @Route("/get-recipes", name="get_recipes")
     * @Method({"GET"})
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getRecipesAction(Request $request)
    {
        $currentUser = $this->getUser();
        if (!$currentUser instanceof User) {
            throw new AccessDeniedHttpException();
        }

        $user = $currentUser->isAssistant() ? $currentUser->getGymAdmin() : $currentUser;

        $query = $request->query;
        $locale                 = (string)  $query->get('locale');
        $q                      = (string)  $query->get('q', null);
        $avoid                  = (string)  $query->get('avoid', null);
        $macroSplit             = (int)     $query->get('macroSplit', null);
        $type                   = (int)     $query->get('type', null);
        $cookingTime            = (int)     $query->get('cookingTime', null);
        $plan                   = (int)     $query->get('plan');
        $mealPlan               = (int)     $query->get('mealPlan');
        $limit                  = (int)     $query->get('limit', '20');
        $offset                 = (int)     $query->get('offset', '0');
        $filterByUser           = (bool)    $query->get('user', '0');
        $favorites              = (bool)    $query->get('favorites', null);
        $considerIngredients    = (bool)    $query->get('considerIngredients', '0');

        $foodPreferences = [];
        $ingredientsToExclude = [];

        $plan = $this
            ->masterMealPlanRepository
            ->find($plan);

        $foodPreferences = [];
        $ingredientsToExclude = [];

        if ($plan != null) {
            $foodPreferences = $plan->getParameterByKey('foodPreferences') ?
                $plan->getParameterByKey('foodPreferences') :
                [];

            $ingredientsToExclude = $plan->getParameterByKey('excludeIngredients') ?
                $plan->getParameterByKey('excludeIngredients') :
                [];
        }


        if (!!$avoid && is_string($avoid)) {
            $avoid = explode(',', $avoid);
            $foodPreferences = array_merge(
                $foodPreferences,
                $this->recipesService->transformFoodPreferences($avoid)
            );
        }

        $params = [
            'q'                     => $q,
            'mealPlan'             => $mealPlan,
            'type'                  => $type,
            'locale'                => $locale,
            'macroSplit'            => $macroSplit,
            'cookingTime'           => $cookingTime,
            'userId'                => (int) $user->getId(),
            'filterByUser'          => $filterByUser,
            'onlyIds'               => false,
            'ingredientsToExclude'  => $ingredientsToExclude,
            'foodPreferences'       => $foodPreferences,
            'favorites'             => $favorites,
            'considerIngredients'   => $considerIngredients,
            'limit'                 => $limit,
            'offset'                => $offset,
            'orderBy'               => 'name'
        ];

        $locale = $plan ? $plan->getLocale() : 'en';

        $recipes = $this->recipeRepository->getAllRecipes($params);

        $lastUsedDates = [];
        if ($plan !== null) {
            $client = $plan->getClient();
            if ($client !== null) {
                $recipesIds = array_unique(array_map(static fn($item) => $item['id'], $recipes));
                $lastUsedDates = $this->recipeRepository->getLastUsedDates($client, $recipesIds);
            }
        }

        $recipes = collect($recipes)
            ->map(function($recipe) use ($locale, $lastUsedDates) {
                return $this->recipeTransformer->transform($recipe, $locale, isset($lastUsedDates[$recipe['id']]) ? $lastUsedDates[$recipe['id']]['date']: null);
            });

        return new JsonResponse($recipes->toArray());
    }

    /**
     * @Route("/update-cooking-time", name="update_cooking_time")
     * @Method({"POST"})
     *
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function updateCookingTimeAction(Request $request)
    {
        $recipe = $this
            ->recipeRepository
            ->find($request->request->get('id'));

        if ($recipe) {
            $recipe->setCookingTime($request->request->get('cooking_time'));
            $this->em->flush();
        }

        return new JsonResponse('OK');
    }

    /**
     * @Route("/update-user-recipe-preferences", name="update_user_recipe_preferences")
     * @Method({"POST"})
     *
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function updateUserRecipePreferencesAction(Request $request)
    {
        $currentUser = $this->getUser();
        if (!$currentUser instanceof User) {
            throw new AccessDeniedHttpException();
        }

        $user = $currentUser->isAssistant() ? $currentUser->getGymAdmin() : $currentUser;

        $recipe = $this
            ->recipeRepository
            ->find($request->request->get('id'));

        if (!$recipe) {
            return new JsonResponse([
                'message' => 'Recipe not found',
            ], JsonResponse::HTTP_NOT_FOUND);
        }

        $recipePreference = $this
            ->recipesService
            ->updateUserRecipePreferences(
                $recipe,
                $user,
                $request->request->get('option')
            );

        return new JsonResponse([
            'favorite' => (bool)$recipePreference->getFavorite(),
            'dislike' => (bool)$recipePreference->getDislike(),
        ]);
    }

    /**
     * @Route("/get-ingredients", name="get_recipe_ingredients")
     * @Method({"GET"})
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getRecipeIngredientsAction(Request $request)
    {
        $query = (string) $request->query->get('q');
        if ($query === '') {
            $ingredients = [];
        } else {
            /** @var Language $language */
            $language = $this->languageRepository->findOneBy(['locale' => $request->query->get('locale', Language::LOCALE_EN)]);
            $ingredients = $this
                ->recipeRepository
                ->getIngredientsInRecipes($query, $language);
        }
        return new JsonResponse($ingredients);
    }
}
