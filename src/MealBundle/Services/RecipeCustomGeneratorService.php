<?php

namespace MealBundle\Services;

use AppBundle\Entity\MealProduct;
use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Support\Arr;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use AppBundle\Entity\MasterMealPlan;
use MealBundle\Services\RecipeBaseService;
use AppBundle\Repository\RecipeRepository;
use AppBundle\Entity\Recipe;
use AppBundle\Entity\MealPlan;

class RecipeCustomGeneratorService
{
    private EntityManagerInterface $em;
    public RecipeBaseService $recipeBaseService;
    private RecipeRepository $recipeRepository;
    private string $projectRoot;

    public function __construct(
        EntityManagerInterface $em,
        string $projectRoot,
        RecipeBaseService $recipeBaseService,
        RecipeRepository $recipeRepository
    ) {
        $this->em = $em;
        $this->projectRoot = $projectRoot;
        $this->recipeBaseService = $recipeBaseService;
        $this->recipeRepository = $recipeRepository;
    }

    /**
     * @param MealPlan|Recipe $meal
     * @param array<mixed> $recipeMacros
     * @param array<mixed> $currentRecipes
     * @return array<mixed>
     */
    public function attemptToHitMacros(
        $meal,
        array $recipeMacros,
        int $recipeType,
        array &$currentRecipes,
        int &$attempt,
        bool $tryOtherRecipesOnFailure = true
    ): array
    {
        $attempt += 1;

        if ($attempt >= 30) {
            throw new HttpException(422, 'Plan could not be generated. No more tries allowed. Please try other macros.');
        }

        try {
            $params = $this->prepareIngredients($meal);
        } catch (\Exception $e) {
            if ($tryOtherRecipesOnFailure && $meal instanceof Recipe) {
                return $this->getNewRecipe($meal, $recipeMacros, $recipeType, $currentRecipes, $attempt);
            } else {
                throw new HttpException(422, 'Could not find suitable ingredients.');
            }
        }

        $params['target'] = $recipeMacros;
        $transformedParams = escapeshellarg(json_encode($params, JSON_THROW_ON_ERROR));

        $projectRoot = $this->projectRoot;
        $file = $projectRoot . "/python/macro_split.py";

        if (!file_exists($file)) {
            throw new HttpException(422, 'Python script not found!');
        }

        // pass the data to numpy to solve the linear system
        $process = Process::fromShellCommandline("python3 $file $transformedParams 2>&1");
        $process->setTimeout(3600);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new HttpException(500, (new ProcessFailedException($process))->getProcess()->getErrorOutput());
        }

        $coefficients = $process->getOutput();

        if (empty($coefficients)) {
            //no suitable coefficients could be found, or there was an error in Python script
            if ($tryOtherRecipesOnFailure && $meal instanceof Recipe) {
                return $this->getNewRecipe($meal, $recipeMacros, $recipeType, $currentRecipes, $attempt);
            } else {
                throw new HttpException(422, 'Could not hit macros for this recipe.');
            }
        }

        $result['ratios'] = (array) json_decode($coefficients, true, 512, JSON_THROW_ON_ERROR);
        $result['recipe'] = $meal->getId();

        return $result;
    }

    /**
     * @param array<mixed> $macros
     * @return array<mixed>
     */
    public function prepareRecipeMacros(array $macros, int $numberOfMeals, int $type, ?MealPlan $meal = null): array
    {
        $percentage = $numberOfMeals === 0 ? 1 : 1 / $numberOfMeals;

        if ($meal && $meal->getPercentWeight()) {
            $percentage = $meal->getPercentWeight();
        } elseif (isset(MealPlan::MEAL_TYPE_PERCENTAGE[$numberOfMeals][$type])) {
            $percentage = MealPlan::MEAL_TYPE_PERCENTAGE[$numberOfMeals][$type];
        }

        return collect($macros)
            ->only(['carbohydrate', 'protein', 'fat'])
            ->map(function ($value) use ($percentage) {
                return $percentage * $value;
            })
            ->toArray();
    }

    /**
     * @param MealPlan|Recipe $meal
     * @return array<mixed>
     */
    public function checkIfMealOrRecipeCanHitMacros($meal, int $type, MealPlan $parent = null): array
    {
        $macros = $this->recipeBaseService->macros;
        $numberOfMeals = $this->recipeBaseService->numberOfMeals;
        $recipeMacros = $this->prepareRecipeMacros($macros, $numberOfMeals, $type, $parent);

        $currentRecipes = [];
        $attempt = 0;
        return $this->attemptToHitMacros($meal, $recipeMacros, $type, $currentRecipes, $attempt, false);
    }

    /** @param array<mixed> $macros */
    public function findClosestMacroSplit(array $macros): int
    {
        $denominator = $this->getKcalsFromMacros($macros);

        $macros = [
            'carbohydrate' => $macros['carbohydrate'] * 4 / $denominator,
            'protein' => $macros['protein'] * 4 / $denominator,
            'fat' => $macros['fat'] * 9 / $denominator
        ];

        $results = [];
        foreach(MealPlan::MACRO_SPLIT as $key => $macroSplitValues) {
            foreach($macros as $macro => $value) {
                $results[$key][$macro] = abs($value - $macroSplitValues[$macro]);
            }
        }

        $totals = [];
        foreach($results as $key => $values) {
            $count = 0;
            foreach($values as $value) {
                $count += $value;
            }
            $totals[$key] = $count;
        }

        return array_keys($totals, min($totals))[0];
    }

    /** @param array<mixed> $macros */
    public function adjustMacrosForCustomPlan(array $macros, int $numberOfMeals): void
    {
        foreach($this->recipeBaseService->plan->getRecipes() as $meal) {
            $recipeMacros = $this->prepareRecipeMacros($macros, $numberOfMeals, $meal->getType(), $meal->getParent());

            $currentRecipes = [];
            $attempt = 0;

            try {
                $result = $this->attemptToHitMacros($meal, $recipeMacros, $meal->getType(), $currentRecipes, $attempt, false);
                $ratio = $result['ratios'];

                //loop through all ingredients in the meal and apply ratio
                foreach($meal->getProducts() as $product) {
                    $ingRatio = $this
                        ->recipeBaseService
                        ->getRatio($ratio, $product);

                    $this
                        ->recipeBaseService
                        ->adjustFoodProductWeight(null, $ingRatio, $product);
                }

                $this->em->flush();

            } catch (\Exception $e) {
            } catch (\Throwable $e) {}
        }

        //update plan to closest macro split + update parameters
        $closestMacroSplit = $this->findClosestMacroSplit($macros);
        $planParameters = $this->recipeBaseService->plan->getParameters();
        $parameters = [];
        if ($planParameters !== null) {
            $parameters = json_decode($planParameters, true, 512, JSON_THROW_ON_ERROR);
        }
        $parameters['macros'] = $macros;
        $kcals = $this->getKcalsFromMacros($macros);

        $this
            ->recipeBaseService
            ->plan
            ->setMacroSplit($closestMacroSplit)
            ->setParameters(json_encode($parameters, JSON_THROW_ON_ERROR))
            ->setDesiredKcals($kcals);

        $this->em->flush();
    }

    /** @param array<mixed> $macros */
    private function getKcalsFromMacros(array $macros): int
    {
        return (int) $macros['carbohydrate'] * 4 + $macros['protein'] * 4 + $macros['fat'] * 9;
    }

    /**
     * @param array<mixed> $recipeMacros
     * @param array<mixed> $currentRecipes
     * @return array<mixed>
     */
    private function getNewRecipe(Recipe $recipe, array $recipeMacros, int $recipeType, array &$currentRecipes, int &$attempt): array
    {
        list($recipes, $scoreMap) = $this
            ->recipeBaseService
            ->retrieveSortedRecipes($recipeType, $this->recipeBaseService->plan->getMacroSplit());

        $recipesByType = isset($recipes[$recipeType]) ? $recipes[$recipeType] : [];
        $randomRecipeId = Arr::first($this->recipeBaseService->randomRecipes($recipesByType, $currentRecipes, 1, $scoreMap));

        $newRecipe = $this
            ->recipeRepository
            ->find($randomRecipeId);

        if ($newRecipe === null) {
            throw new NotFoundHttpException();
        }

        $currentRecipes[$newRecipe->getId()] = $currentRecipes[$recipe->getId()];
        $currentRecipes[$newRecipe->getId()] = $newRecipe->getId();

        unset($currentRecipes[$recipe->getId()]);
        return $this->attemptToHitMacros($newRecipe, $recipeMacros, $recipeType, $currentRecipes, $attempt);
    }

    /** @return array<mixed> */
    private function prepareIngredients(Recipe|MealPlan $meal): array
    {
        $tweakableMacros = [
            'protein' => [
                'id' => 0,
                'value' => 0
            ],
            'carbohydrate' => [
                'id' => 0,
                'value' => 0
            ],
            'fat' => [
                'id' => 0,
                'value' => 0
            ]
        ];

        $recipeProducts = $meal->getProducts();
        $products = collect();

        foreach ($recipeProducts as $recipeProduct) {
            $products->put($recipeProduct->getId(), $product = $recipeProduct->getProduct());

            $protein = $product->getProtein();
            $carbs = $product->getCarbohydrates();
            $fat = $product->getFat();
            $macrosTest = ['protein' => $protein * 4, 'carbohydrate' => $carbs * 4, 'fat' => $fat * 9];
            $largestMacro = array_keys($macrosTest, max($macrosTest))[0];

            if ($macrosTest[$largestMacro] > $tweakableMacros[$largestMacro]['value']) {
                $tweakableMacros[$largestMacro]['value'] = $macrosTest[$largestMacro];
                $tweakableMacros[$largestMacro]['id'] = $product->getId();
            }
        }

        $tweakableIngredients = [];
        foreach ($tweakableMacros as $tweakableMacro => $ingredient) {
            if ($ingredient['value'] == 0) continue;
            $tweakableIngredients[] = $ingredient['id'];
        }

        if (count($tweakableIngredients) != 3) {
            throw new HttpException(422, 'No clear macro ingredients.');
        }

        //select the three ingredients + macro metrics that we want to adjust + the remaining ingredients in the recipe
        $plan = ['ingredients' => []];

        foreach ($recipeProducts as $recipeProduct) {
            $product = $products->get($recipeProduct->getId());
            $totalWeight = $recipeProduct->getTotalWeight();

            $protein = round($product->getProtein() / 100 * $totalWeight);
            $carbs = round($product->getCarbohydrates() / 100 * $totalWeight);
            $fat = round($product->getFat() / 100 * $totalWeight);

            $plan['ingredients'][] = [
                'id' => $product->getId(),
                'macros' => [
                    'carbohydrate' => $carbs,
                    'protein' => $protein,
                    'fat' => $fat
                ],
                'tweak' => in_array($product->getId(), $tweakableIngredients) ? true : false
            ];
        }

        return $plan;
    }

    public function updateIngredientWeightsForCustomMacros(MasterMealPlan $plan, ?MealPlan $meal = null, MealPlan $parent = null): void
    {
        $parentMeals = $plan->getMealPlansWhereParentIsNull();

        if ($meal) {
            //we want to update one specific meal only
            $this->updateIngredientsHelper($meal, $parent);
        } else {
            //loop through all parent meal plans
            foreach ($parentMeals as $parentMeal) {
                foreach ($parentMeal->getChildren() as $child) {
                    $this->updateIngredientsHelper($child, $parentMeal);
                }
            }
        }

    }

    private function updateIngredientsHelper(MealPlan $meal, ?MealPlan $parent = null): void
    {
        $parentMeal = $parent === null ? $parent : $meal->getParent();

        if ($parentMeal === null) {
            return;
        }

        $this
            ->recipeBaseService
            ->setNumberOfMeals(count($parentMeal->getMasterMealPlan()->getMealPlansWhereParentIsNull()));

        try {
            $recipe = $meal->getRecipe();
            if ($recipe === null) {
                throw new \RuntimeException();
            }

            $ratio = $this
                ->checkIfMealOrRecipeCanHitMacros($recipe, $parentMeal->getType(), $parentMeal);

            //loop through all ingredients in the meal and apply ratio
            foreach($meal->getProducts() as $product) {
                $ingRatio = $this
                    ->recipeBaseService
                    ->getRatio($ratio['ratios'], $product);

                $this
                    ->recipeBaseService
                    ->adjustFoodProductWeight(null, $ingRatio, $product);
                }
        } catch (\Exception $e) {}
    }

}
