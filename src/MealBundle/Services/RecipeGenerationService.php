<?php

namespace MealBundle\Services;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Arr;
use AppBundle\Repository\RecipeRepository;
use AppBundle\Repository\MealPlanRepository;
use AppBundle\Entity\MasterMealPlan;
use AppBundle\Entity\Recipe;
use AppBundle\Entity\MealPlan;
use AppBundle\Entity\User;
use AppBundle\Entity\Client;
use AppBundle\Entity\MealPlanProduct;
use AppBundle\Services\MealPlanService;
use AppBundle\Services\RecipesService;
use AppBundle\Services\ValidationService;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use MealBundle\Services\RecipeBaseService;
use MealBundle\Services\RecipeCustomGeneratorService;

class RecipeGenerationService
{
    private EntityManagerInterface $em;
    private TranslatorInterface $translator;
    private RecipeRepository $recipeRepository;
    private MealPlanRepository $mealPlanRepository;
    private MealPlanService $mealPlanService;
    private ValidationService $validationService;
    private RecipesService $recipesService;
    public RecipeBaseService $recipeBaseService;
    private RecipeCustomGeneratorService $recipeCustomGeneratorService;

    public function __construct(
        EntityManagerInterface $em,
        TranslatorInterface $translator,
        RecipeRepository $recipeRepository,
        MealPlanRepository $mealPlanRepository,
        MealPlanService $mealPlanService,
        RecipesService $recipesService,
        ValidationService $validationService,
        RecipeBaseService $recipeBaseService,
        RecipeCustomGeneratorService $recipeCustomGeneratorService
    ) {
        $this->em = $em;
        $this->translator = $translator;
        $this->recipeRepository = $recipeRepository;
        $this->mealPlanRepository = $mealPlanRepository;
        $this->mealPlanService = $mealPlanService;
        $this->recipesService = $recipesService;
        $this->validationService = $validationService;
        $this->recipeBaseService = $recipeBaseService;
        $this->recipeCustomGeneratorService = $recipeCustomGeneratorService;
    }

    /** @param mixed $val */
    public function setRecipeBaseProperty(string $key, $val): self
    {
        $setter = 'set' . ucfirst($key);
        if (method_exists($this->recipeBaseService, $setter)) {
            $this->recipeBaseService->$setter($val);
        }

        return $this;
    }

    public function generatePlan(): MasterMealPlan
    {
        //get recipes
        $recipes = $this
            ->recipeBaseService
            ->generateRandomRecipes();

        $recipeCollection = collect($recipes)->pluck('recipe', 'recipe')->toArray();
        $recipeList = collect($this->recipeRepository->findBy(['id' => array_values($recipeCollection)]))
            ->keyBy(function (Recipe $recipe) {
                return $recipe->getId();
            });

        $order = 1;
        foreach ($recipes as $recipe) {
            $ratio = $recipe['ratio'];
            $recipeEntity = $recipeList->get($recipe['recipe']);
            $parent = $this
                ->mealPlanRepository
                ->find($recipe['parent']);

            try {
                if ($this->recipeBaseService->type === MasterMealPlan::TYPE_CUSTOM_MACROS) {
                    $attempt = 0;
                    $recipeMacros = $this
                        ->recipeCustomGeneratorService
                        ->prepareRecipeMacros($this->recipeBaseService->macros, $this->recipeBaseService->numberOfMeals, $recipe['type']);

                    $result = $this
                        ->recipeCustomGeneratorService
                        ->attemptToHitMacros($recipeEntity, $recipeMacros, $recipe['type'], $recipeCollection, $attempt);

                    $recipeEntity = $result['recipe'] === $recipeEntity->getId() ?
                        $recipeEntity :
                        $this->recipeRepository->find($result['recipe']);

                    $ratio = $result['ratios'];
                }
            } catch (\Exception $e) {
                throw new HttpException(422, $e->getMessage());
            }

            $this->updateMealPlan($recipeEntity, $order, $ratio, $recipe['type'], $parent);
            $order++;
        }

        return $this->recipeBaseService->plan;
    }

    /** @param array<mixed> $body */
    public function preparePlan(array $body, User $user, Client $client): MasterMealPlan
    {
        $name = $body['name'];
        $alternatives = $body['alternatives'];
        $numberOfMeals = $body['numberOfMeals'];
        $type = null;
        if (isset($body['type'])) {
            $type = (int) $body['type'];
        }
        $desiredKcals = $body['desiredKcals'];
        $macros = (array) $body['macros'];
        $macroSplit = $body['macroSplit'];
        $avoid = $body['avoid'];
        $prioritize = $body['prioritize'];
        $exclude = $body['excludeIngredients'];
        $locale = $body['locale'];

        $validationService = $this->validationService;
        $validationService->checkEmptyString($name, 'The title is empty.');
        $validationService->checkEmptyString($alternatives, 'The # of alternatives is not defined.');
        $validationService->checkEmptyString($numberOfMeals, 'The # of meals is not defined.');
        $validationService->checkEmptyString($type, 'You need to select how to generate meal plan.');
        $validationService->checkEmptyString($desiredKcals, 'You need to select desired kcals.');

        if ($type === MasterMealPlan::TYPE_CUSTOM_MACROS) {
            $validationService->checkEmptyString($macros['carbohydrate'], 'You need to specify # of carbs.');
            $validationService->checkEmptyString($macros['protein'], 'You need to specify # of protein.');
            $validationService->checkEmptyString($macros['fat'], 'You need to specify # of fat.');
            $macroSplit = $this->recipeCustomGeneratorService->findClosestMacroSplit($macros);
        }

        $foodPreferences = $this->recipesService->transformFoodPreferences($avoid);
        $ingredientsToExclude = collect($exclude)->map(function ($val) {
            return (int)$val;
        })->filter(function ($val) {
            return $val > 0;
        })->toArray();

        $parameters = [
            'foodPreferences' => $foodPreferences,
            'excludeIngredients' => $ingredientsToExclude,
            'macros' => $macros
        ];

        $plan = $this
            ->mealPlanService
            ->createMasterPlan(
                $name,
                null,
                $user,
                $client,
                null,
                [],
                $desiredKcals,
                $macroSplit,
                $locale,
                true,
                $parameters,
                $type,
                true
            );

        $parameters['plan'] = $plan;
        $settings = array_merge($body, $parameters);

        foreach ($settings as $key => $val) {
            $this->setRecipeBaseProperty($key, $val);
        }

        return $plan;
    }

    /** @param float|array<mixed> $ratio */
    private function updateMealPlan(Recipe $recipe, int $order, $ratio, int $type, ?MealPlan $parent): void
    {
        $this
            ->recipeBaseService
            ->convertRecipeIntoMealPlan($recipe, $order, $parent, $ratio, $type);
        $this->em->flush();
        $this->em->refresh($this->recipeBaseService->plan);
    }

    public function syncPlan(bool $syncMultiple = true, ?int $type = null, ?MealPlan $meal = null): void
    {
        if ($syncMultiple) {
            foreach ($this->recipeBaseService->plan->getRecipes($type) as $planMeal) {
                $this
                    ->recipeBaseService
                    ->syncPlanHelper($planMeal);
            }
        } elseif ($meal !== null) {
            $this
                ->recipeBaseService
                ->syncPlanHelper($meal);
        }
    }

    public function addRecipeToMealPlan(
        int $id,
        ?MealPlan $replaceMeal,
        int $type,
        MealPlan $parent,
        ?float $ratio
    ): MealPlan
    {
        $recipe = $this
            ->recipeRepository
            ->find($id);

        if ($recipe === null) {
            throw new NotFoundHttpException('Recipe not found');
        }

        if ($ratio == null) {
            $ratio = $this
                ->recipeBaseService
                ->getMealRatio($recipe->getKcals(), $parent);
        }

        if ($ratio == null) {
            $ratio = (float) 0;
        }

        if ($replaceMeal !== null) {
            $order = $replaceMeal->getOrder();
        } else {
            $order = $this
                ->mealPlanRepository
                ->getLastOrderByPlan($this->recipeBaseService->plan) + 1;
        }

        $meal = $this
            ->recipeBaseService
            ->convertRecipeIntoMealPlan($recipe, $order, $parent, $ratio, $type);
        $this->em->flush();
        $this->em->refresh($meal);
        return $meal;
    }

    public function regenerateRandomMeals(int $type, int $macroSplit, MealPlan $parent): void
    {
        $alternativesPerMeal = 0;
        $existingKcals = 0;
        $orders = [];
        // delete existing recipes
        $existingRecipes = $this->recipeBaseService->plan->getRecipes($type);
        foreach ($existingRecipes as $existingRecipe) {
            $alternativesPerMeal++;
            $existingRecipe->setDeleted(true);
            $existingKcals = $existingRecipe->getTotals()['kcal'];
            $orders[] = $existingRecipe->getOrder();
        }

        $this->recipeBaseService->alternatives = $alternativesPerMeal;

        list($recipes, $scoreMap) = $this
            ->recipeBaseService
            ->retrieveSortedRecipes($type, $macroSplit);

        $meals[] = ['type' => $type, 'percentage' => 0];
        $randomRecipes = $this
            ->recipeBaseService
            ->setAlternatives($alternativesPerMeal)
            ->loopRandomRecipes($meals, $recipes, $scoreMap, false);

        $i = 0;
        foreach ($randomRecipes[$type]['meals'] as $recipe) {
            $recipeEntity = $this
                ->recipeRepository
                ->find($recipe['recipe']);

            if ($recipeEntity === null) {
                throw new NotFoundHttpException('Recipe not found');
            }

            $ratio = $existingKcals / $recipe['kcals'];
            $meal = $this
                ->recipeBaseService
                ->convertRecipeIntoMealPlan($recipeEntity, $orders[$i], $parent, $ratio, $type);
            $i++;
            $this->em->refresh($meal);
        }

        $this->em->flush();
        $this->em->refresh($this->recipeBaseService->plan);
    }

    public function updateKcalsInPlan(bool $approved): void
    {
        if ($this->recipeBaseService->type === MasterMealPlan::TYPE_CUSTOM_MACROS) {
            if ($approved) {
                //user approved changes to meals
                //so we update all macros
                $this
                    ->recipeCustomGeneratorService
                    ->adjustMacrosForCustomPlan($this->recipeBaseService->macros, $this->recipeBaseService->numberOfMeals);
            } else {
                //loop through all meals
                //and check if we are able to hit macros
                $errors = 0;
                foreach($this->recipeBaseService->plan->getRecipes() as $meal) {
                    try {
                        $this
                            ->recipeCustomGeneratorService
                            ->checkIfMealOrRecipeCanHitMacros($meal, $meal->getType());
                    } catch (\Exception $e) {
                        $errors += 1;
                    }
                }

                if ($errors === 0) {
                    $this
                        ->recipeCustomGeneratorService
                        ->adjustMacrosForCustomPlan($this->recipeBaseService->macros, $this->recipeBaseService->numberOfMeals);
                } else {
                    throw new HttpException(422, (string) $errors);
                }
            }
        } else {
            $this->syncPlan();
        }
    }

    public function updatePlan(MealPlan $meal = null, MealPlan $parent = null): void
    {
        if ($this->recipeBaseService->type === MasterMealPlan::TYPE_CUSTOM_MACROS) {
            $this->recipeCustomGeneratorService->updateIngredientWeightsForCustomMacros($this->recipeBaseService->plan, $meal, $parent);
        } else {
            if ($meal) {
                $this
                    ->recipeBaseService
                    ->syncPlanHelper($meal);
            } else {
                $this
                    ->syncPlan();
            }
        }
    }
}
