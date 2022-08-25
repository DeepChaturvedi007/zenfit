<?php

namespace MealBundle\Services;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Contracts\Translation\LocaleAwareInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use AppBundle\Repository\RecipeRepository;
use AppBundle\Repository\MealPlanRepository;
use AppBundle\Services\MealPlanService;
use AppBundle\Services\RecipesService;
use Illuminate\Support\Collection;
use Symfony\Component\HttpKernel\Exception\HttpException;
use AppBundle\Entity\MasterMealPlan;
use AppBundle\Entity\Recipe;
use AppBundle\Entity\RecipeProduct;
use AppBundle\Entity\MealPlanProduct;
use AppBundle\Entity\MealPlan;

class RecipeBaseService
{
    private EntityManagerInterface $em;
    private RecipeRepository $recipeRepository;
    private TranslatorInterface $translator;

    public MasterMealPlan $plan;
    public int $alternatives;
    public int $numberOfMeals;
    public int $type;
    public int $macroSplit;
    public string $locale;
    public float $desiredKcals;
    /** @var array<mixed> */
    public array $macros = [];
    /** @var array<mixed> */
    public array $foodPreferences = [];
    /** @var array<mixed> */
    public array $excludeIngredients = [];
    public bool $prioritize = false;

    public function __construct(
        EntityManagerInterface $em,
        RecipeRepository $recipeRepository,
        TranslatorInterface $translator
    ) {
        $this->em = $em;
        $this->recipeRepository = $recipeRepository;
        $this->translator = $translator;
    }

    public function setPlan(MasterMealPlan $plan): self
    {
        $this->plan = $plan;
        return $this;
    }

    public function setAlternatives(int $alternatives): self
    {
        $this->alternatives = $alternatives;
        return $this;
    }

    public function setDesiredKcals(float $desiredKcals): self
    {
        $this->desiredKcals = $desiredKcals;
        return $this;
    }

    public function setNumberOfMeals(int $numberOfMeals): self
    {
        $this->numberOfMeals = $numberOfMeals;
        return $this;
    }

    public function setType(int $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function setMacroSplit(int $macroSplit): self
    {
        $this->macroSplit = $macroSplit;
        return $this;
    }

    public function setLocale(string $locale): self
    {
        $this->locale = $locale;
        return $this;
    }

    /** @param array<mixed> $macros */
    public function setMacros(array $macros): self
    {
        $this->macros = $macros;
        return $this;
    }

    /** @param array<mixed> $foodPreferences */
    public function setFoodPreferences(array $foodPreferences): self
    {
        $this->foodPreferences = $foodPreferences;
        return $this;
    }

    /** @param array<mixed> $excludeIngredients */
    public function setExcludeIngredients(array $excludeIngredients): self
    {
        $this->excludeIngredients = $excludeIngredients;
        return $this;
    }

    public function setPrioritize(bool $prioritize): self
    {
        $this->prioritize = $prioritize;
        return $this;
    }

    /** @param float|array<mixed> $ratio */
    public function convertRecipeIntoMealPlan(Recipe $recipe, int $order, ?MealPlan $parent, $ratio, int $type = 0): MealPlan
    {
        $newMeal = (new MealPlan($this->plan))
            ->setParent($parent)
            ->setOrder($order)
            ->setName($recipe->getName())
            ->setComment($recipe->getComment())
            ->setMacroSplit($recipe->getMacroSplit())
            ->setType($type)
            ->setRecipe($recipe);

        $this->em->persist($newMeal);
        $this->em->flush();

        $recipeProducts = $recipe->getProducts();

        foreach ($recipeProducts as $recipeProduct) {
            $ingRatio = $this->getRatio($ratio, $recipeProduct);

            $mealPlanProduct = $this->createMealPlanProductFromRecipeProduct($recipeProduct);
            $this->adjustFoodProductWeight($newMeal, $ingRatio, $mealPlanProduct);
        }

        return $newMeal;
    }

    /** @param MealPlanProduct|RecipeProduct $product */
    public function adjustFoodProductWeight(?MealPlan $meal, ?float $ratio, $product): void
    {
        // 5 gram intervals
        $weight = round($product->getTotalWeight() * $ratio / 5) * 5;
        $weightUnits = $this->getRoundedWeight($product, $product->getWeightUnits(), (float) $ratio);

        $weight = $weight == 0 ? 1 : $weight;
        $weightUnits = $weightUnits == 0 ? 1 : $weightUnits;

        $productWeight = $product->getWeight();

        if ($productWeight !== null) {
            $weight = $productWeight->getWeight() * $weightUnits;
        }

        $product
            ->setTotalWeight((int) $weight)
            ->setWeightUnits($weightUnits);

        if ($meal !== null and $product instanceof MealPlanProduct) {
            $product->setPlan($meal);
        }

        $this->em->persist($product);
    }

    /** @return array<mixed> */
    public function retrieveSortedRecipes(?int $type, ?int $macroSplit): array
    {
        $repo = $this->recipeRepository;

        $params = [
            'type'                  => $type,
            'locale'                => $this->plan->getLocale(),
            'macroSplit'            => $macroSplit,
            'userId'                => $this->plan->getUser()->getId(),
            'foodPreferences'       => $this->foodPreferences,
            'ingredientsToExclude'  => $this->excludeIngredients,
            'prioritizeByUser'      => $this->prioritize,
            'onlyIds'               => true,
            'groupBy'               => 'type'
        ];

        $recipes = $repo->getAllRecipes($params);
        $client = $this->plan->getClient();
        if ($client === null) {
            throw new \RuntimeException('Plan has no user');
        }
        $lowPriorityForIds = $repo->getUsedRecipesIdsByClient($client);
        $scoreMap = $repo::getScoreMapForRandomizer($recipes, $lowPriorityForIds);
        $recipes = $repo::transformForRandomizer($recipes);
        return [$recipes, $scoreMap];
    }

    /** @return array<mixed> */
    public function generateRandomRecipes(): array
    {
        $macroSplit = $this->plan->getMacroSplit() ?? null;
        list($recipes, $scoreMap) = $this->retrieveSortedRecipes(null, $macroSplit);
        $meals = $this->getMeals();
        $recipes = $this->loopRandomRecipes($meals, $recipes, $scoreMap, true);
        return $this->adjustMealRatios($recipes);
    }

    private function createMealPlanProductFromRecipeProduct(RecipeProduct $recipeProduct): MealPlanProduct
    {
        return (new MealPlanProduct($recipeProduct->getProduct()))
            ->setTotalWeight($recipeProduct->getTotalWeight())
            ->setOrder($recipeProduct->getOrder())
            ->setWeightUnits($recipeProduct->getWeightUnits())
            ->setWeight($recipeProduct->getWeight());
    }

    /**
     * @param array<mixed> $recipes
     * @return array<mixed>
     */
    private function adjustMealRatios(array $recipes): array
    {
        $results = [];

        foreach ($recipes as $type => $recipe) {
            $percentage = $recipe['percentage'];

            foreach ($recipe['meals'] as $meal) {
                $results[] = [
                    'type' => $type,
                    'recipe' => $meal['recipe'],
                    'ratio' => $this->desiredKcals ? $this->desiredKcals / $meal['kcals'] * $percentage : null,
                    'parent' => $meal['parent'],
                ];
            }
        }

        return $results;
    }

    /**
     * @param array<mixed> $meals
     * @param array<mixed> $recipes
     * @param array<mixed> $scoreMap
     * @return array<mixed>
     */
    public function loopRandomRecipes(array $meals, array $recipes, array $scoreMap = [], bool $createParent = false): array
    {
        $meals = collect($meals);
        $repo = $this->recipeRepository;
        $alternatives = $this->alternatives;

        $recipeIds = collect($meals)->reduce(function (Collection $carry, $meal) use ($recipes, $alternatives, $scoreMap, $repo) {
            $recipesByType = isset($recipes[$meal['type']]) ? $recipes[$meal['type']] : [];
            $map = [];
            foreach ($repo->getRecipeTotalKcalsByIds($recipesByType) as $info) {
                $map[$info['id']] = $info['kcals'];
            }
            $allowedRecipes = array_filter($recipesByType, function ($id) use ($map) {
                return $map[$id] > 0;
            });
            $items = $this->randomRecipes($allowedRecipes, $carry->toArray(), $alternatives, $scoreMap);
            return $carry->merge($items);
        }, collect());

        $recipeList = collect($repo->getRecipeTotalKcalsByIds($recipeIds->toArray()))->keyBy('id');

        //initiate translator
        /** @var TranslatorInterface&LocaleAwareInterface $translator */
        $translator = $this->translator;
        $translator->setLocale($this->plan->getLocale());

        if ($createParent) {
            $meals = $meals->map(function ($meal, $index) use ($translator) {
                $order = $index + 1;
                $meal['parent'] = $this->createParent(
                    $translator->trans('meal.types.' . $meal['type']),
                    $order,
                    $meal['type'],
                    $meal['percentage']
                );

                $this->em->persist($meal['parent']);
                return $meal;
            });
        }

        $this->em->flush();

        return collect($meals)->reduce(function ($carry, $meal) use ($alternatives, $recipeList, $recipeIds) {
            $type = $meal['type'];

            $carry[$type]['percentage'] = $meal['percentage'];
            $carry[$type]['meals'] = $recipeIds->splice(0, $alternatives)->map(function ($id) use ($meal, $recipeList) {
                $recipe = $recipeList->get($id);
                $parent = $meal['parent'] ?? null;

                return [
                    'recipe' => $recipe['id'],
                    'kcals' => $recipe['kcals'],
                    'parent' => $parent ? $parent->getId() : null,
                ];
            })->toArray();

            return $carry;
        }, []);
    }

    public function createParent(string $name, int $order = 1, int $type = 0, float $percentWeight = 0.0): MealPlan
    {
        return (new MealPlan($this->plan))
            ->setName($name)
            ->setOrder($order)
            ->setType($type)
            ->setMacroSplit($this->plan->getMacroSplit())
            ->setContainsAlternatives(true)
            ->setPercentWeight($percentWeight);
    }

    /**
     * @param array<mixed> $recipes
     * @param array<mixed> $skipRecipes
     * @param array<mixed> $scoreMap
     * @return array<mixed>
     */
    public function randomRecipes(array $recipes = [], array $skipRecipes = [], int $count = 1, array $scoreMap = []): array
    {
        // Collect incoming array of IDs;
        $recipes = collect($recipes);
        // Omit known recipes inorder to prevent duplicates;
        $recipes = $recipes->diff($skipRecipes);
        // Collect the most prioritized ID's using $scoreMap
        $prioritizedPool = $recipes
            ->filter(function ($id) use ($scoreMap) {
                return (int) $scoreMap[$id] > 0;
            })
            ->sortByDesc(function ($id) use ($scoreMap) {
                return $scoreMap[$id];
            });
        // Collect common recipes for the situations if the $prioritizedPool will contain not enough items
        $commonPool = $recipes->filter(function ($id) use ($scoreMap) {
            return (int) $scoreMap[$id] <= 0;
        });
        // Extract $count items from the prioritized pool
        $availableCount = $prioritizedPool->count();
        $generalPool = $prioritizedPool->slice(0, $count > $availableCount ? $availableCount : $count);
        // Check if the $generalPool has enough items
        if ($generalPool->count() < $count) {
            // Add items to the $generalPool if $prioritizedItems count too low
            $count = $count - $generalPool->count();
            $availableCount = $commonPool->count();
            $additionalPool = $commonPool->random($count > $availableCount ? $availableCount : $count);
            // Merge prioritized and additional
            $generalPool = $generalPool->merge($additionalPool);
        }
        // Check if the final pool has enough items
        if ($generalPool->count() < $count) {
            throw new HttpException(422, 'Not enough recipes match your criteria. Zenfit has been notified.');
        }
        // Return the array of the most relevant IDs
        return $generalPool->toArray();
    }

    /** @param mixed $product */
    private function getRoundedWeight($product, float $weight, float $ratio): float
    {
        if ($product->getProduct()->getAllowSplit()) {
            $productWeight = $product->getWeight();
            if ($productWeight && (
                    strpos($productWeight->getName(), 'ounce') !== false
                    || strpos($productWeight->getName(), 'oz') !== false)
            ) {
                // if weight is in oz, allow for split in smaller units (0,1)
                return round($weight * $ratio * 10) / 10;
            } else {
                // else split in halves (0,5)
                return round($weight * $ratio * 2) / 2;
            }
        }

        return round($weight * $ratio);
    }

    public function getRatio(mixed $ratio, MealPlanProduct|RecipeProduct $product): float
    {
        if (is_array($ratio)) {
            $productId = $product->getProduct()->getId();

            return $ratio[$productId] ?? 1.0;
        }

        //ratio is not array
        return (float) $ratio;
    }

    /** @return array<mixed> */
    public static function getMealTypesByNumber(int $number): array
    {
        static $types = null;

        if (null === $types) {
            $types = [
                3 => [
                    MealPlan::TYPE_BREAKFAST,
                    MealPlan::TYPE_LUNCH,
                    MealPlan::TYPE_DINNER,
                ],
                4 => [
                    MealPlan::TYPE_BREAKFAST,
                    MealPlan::TYPE_LUNCH,
                    MealPlan::TYPE_DINNER,
                    MealPlan::TYPE_EVENING_SNACK,
                ],
                5 => [
                    MealPlan::TYPE_BREAKFAST,
                    MealPlan::TYPE_MORNING_SNACK,
                    MealPlan::TYPE_LUNCH,
                    MealPlan::TYPE_AFTERNOON_SNACK,
                    MealPlan::TYPE_DINNER,
                ],
                6 => [
                    MealPlan::TYPE_BREAKFAST,
                    MealPlan::TYPE_MORNING_SNACK,
                    MealPlan::TYPE_LUNCH,
                    MealPlan::TYPE_AFTERNOON_SNACK,
                    MealPlan::TYPE_DINNER,
                    MealPlan::TYPE_EVENING_SNACK,
                ],
            ];
        }

        return $types[$number] ?? [];
    }

    public function syncPlanHelper(MealPlan $meal): void
    {
        $this->em->refresh($meal);
        $mealPlanParent = $meal->getParent();

        if ($mealPlanParent === null) {
            throw new \RuntimeException('Meal has no parent');
        }

        $ratio = $this->getMealRatio($meal->getTotals()['kcal'], $mealPlanParent);

        // Get total kcals in the meal / recipe
        foreach ($meal->getProducts() as $mealPlanProduct) {
            // Apply the new recipe ratio to the meal products in the plan
            $this->adjustFoodProductWeight(null, $ratio, $mealPlanProduct);
        }

        $this->em->flush();
    }

    /** @return array<mixed> */
    private function getMeals(): array
    {
        $types = static::getMealTypesByNumber($this->numberOfMeals);

        return collect($types)
            ->zip(MealPlan::MEAL_PERCENTAGE_SPLIT[$this->numberOfMeals] ?? [])
            ->map(function ($item) {
                list ($type, $percentage) = $item;
                return [
                    'type' => $type,
                    'percentage' => $percentage,
                ];
            })
            ->all();
    }

    public function getMealRatio(float $currentKcals, MealPlan $parent): float
    {
        if ($currentKcals == 0) {
            throw new HttpException(422, 'Cant adjust a recipe with 0 kcals. Please choose another recipe.');
        }

        return (float) rescue(function () use ($currentKcals, $parent) {
            $desiredKcals = $this->plan->getDesiredKcals();

            $parentPercentWeight = $parent->getPercentWeight();
            if ($parentPercentWeight !== null) {
                return (float) $parentPercentWeight * $desiredKcals / $currentKcals;
            }

            $numberOfMeals = count($this->plan->getMealPlansWhereParentIsNull());

            if (!isset(MealPlan::MEAL_TYPE_PERCENTAGE[$numberOfMeals][$parent->getType()])) {
                return 0;
            }

            return (MealPlan::MEAL_TYPE_PERCENTAGE[$numberOfMeals][$parent->getType()] * $desiredKcals) / $currentKcals;
        }, 0);
    }
}
