<?php

namespace MealBundle\Helper;

use MealBundle\Services\RecipeCustomGeneratorService;
use AppBundle\Entity\MealPlan;
use AppBundle\Entity\MealPlanProduct;
use MealBundle\Transformer\MealProductTransformer;
use AppBundle\Entity\MasterMealPlan;
use AppBundle\Entity\Recipe;
use Carbon\Carbon;

class MealHelper
{
    /**
     * @param MealPlan $meal
     */
    private $meal;

    private RecipeCustomGeneratorService $recipeCustomGeneratorService;

    public function __construct(RecipeCustomGeneratorService $recipeCustomGeneratorService)
    {
        $this->recipeCustomGeneratorService = $recipeCustomGeneratorService;
    }

    public function setMeal(MealPlan $meal)
    {
        $this->meal = $meal;
        return $this;
    }

    public function serializePlans($plans)
    {
        $results = [];
        foreach ($plans as $masterMealPlan) {
            /** @var MasterMealPlan $masterMealPlan */
            $mealPlans = $this->serializeMealPlans($masterMealPlan);
            $mealPlans = $masterMealPlan->getContainsAlternatives() ? $this->transformParentMealPlans($mealPlans) : $mealPlans;
            $macroSplit = $this->planMacroSplit($masterMealPlan, $mealPlans);
            $mealsFitKcals = $this->checkMealPlanKcalFit($mealPlans);
            $avgTotals = $this->planAvgTotals($mealPlans);
            $meta = $this->serializeMasterMealPlanMeta($masterMealPlan);

            if ($started = $masterMealPlan->getStarted()) {
                $started = $started->format('Y-m-d H:i:s');
            }

            $image = $masterMealPlan->getImage();

            if (!$image) {
                $meal = rescue(static function () use ($masterMealPlan) {
                    $mealPlans = $masterMealPlan->getMealPlansWhereParentIsNull();
                    if (array_key_exists(0, $mealPlans)) {
                        /** @var MealPlan $mealPlan */
                        $mealPlan = $mealPlans[0];
                        $child = $mealPlan->getChildren()->first();

                        return $child === false ? null : $child;
                    }

                    return null;
                });

                $image = $meal && $meal->getImage() ? $meal->getImage() : null;
                $image = $meal && $meal->getRecipe() ? $meal->getRecipe()->getImage() : $image;
            }

            $item = [
                'id' => $masterMealPlan->getId(),
                'name' => $masterMealPlan->getName(),
                'explaination' => $masterMealPlan->getExplaination() == "" ? null : $masterMealPlan->getExplaination(),
                'active' => $masterMealPlan->getStatus() === MasterMealPlan::STATUS_ACTIVE,
                'last_updated' => Carbon::instance($masterMealPlan->getLastUpdated())->toDateTimeString(),
                'locale' => $masterMealPlan->getLocale(),
                'meal_plans' => $mealPlans,
                'macro_split' => $macroSplit,
                'created' => Carbon::instance($masterMealPlan->getCreatedAt())->toDateTimeString(),
                'client_name' => $masterMealPlan->getClient() ? $masterMealPlan->getClient()->getFirstName() : null,
                'client' => $masterMealPlan->getClient() ? $masterMealPlan->getClient()->getId() : null,
                'desired_kcals' => $masterMealPlan->getDesiredKcals(),
                'contains_alternatives' => $masterMealPlan->getContainsAlternatives(),
                'meals_fit_kcals' => $mealsFitKcals,
                'avg_totals' => $avgTotals,
                'user' => $masterMealPlan->getUser()->getFirstName(),
                'image' => $image,
                'meta' => $meta,
                'started' => $started,
                'type' => $masterMealPlan->getType(),
                'parameters' => $masterMealPlan->getParameters()
            ];

            array_push($results, $item);
        }

        return $results;
    }

    public function serializeMealPlans(MasterMealPlan $masterMealPlan)
    {
        $locale = $masterMealPlan->getLocale();
        $plans = $masterMealPlan->getMealPlansWhereParentIsNull();
        $numberOfPlans = count($plans);

        $mealPlans = [];
        foreach ($plans as $plan) {
            /** @var MealPlan $plan */

            $last = false;
            if ($this->meal && $this->meal->getParent()->getId() == $plan->getId()) {
                $last = true;
            }
            if ($numberOfPlans == 1) {
                $last = true;
            }

            $recipe = $plan->getRecipe();
            $mealPlans[$plan->getId()] = array(
                'id' => $plan->getId(),
                'name' => $plan->getName(),
                'comment' => $plan->getComment(),
                'image' => $recipe ? $recipe->getImage() : $plan->getImage(),
                'order' => $plan->getOrder(),
                'type' => $plan->getType(),
                'locale' => $locale,
                'macroSplit' => $plan->getMacroSplit(),
                'totals' => $plan->getMealsTotal(),
                'meals' => $this->transformPlanMeals($plan, $locale),
                'contains_alternatives' => $plan->getContainsAlternatives(),
                'desired_kcals' => $masterMealPlan->getDesiredKcals(),
                'macro_slit' => $plan->getMacroSplit(),
                'ideal_totals' => $this->idealTotals($masterMealPlan, $plan),
                'percent_weight' => $plan->getPercentWeight() ? (double)$plan->getPercentWeight() : 0,
                'last' => $last
            );
        }

        return $mealPlans;
    }


    /**
     * @param MealPlan $plan
     * @param string $locale
     * @return array
     */
    public function transformPlanMeals(MealPlan $plan, $locale = 'en')
    {
        $res = [];
        /** @var MealPlan $meal */
        foreach ($plan->getChildren()->toArray() as $meal) {
            if ($meal->getDeleted()) {
                continue;
            }

            $idealKcals = 0;
            $masterMealPlan = $plan->getMasterMealPlan();
            if ($masterMealPlan->getContainsAlternatives()) {
                $numberOfMeals = count($masterMealPlan->getMealPlansWhereParentIsNull());
                $percentWeight = $plan->getPercentWeight();
                if ($percentWeight !== null) {
                    $idealKcals = round($masterMealPlan->getDesiredKcals() * (float) $percentWeight);
                } elseif (isset(MealPlan::MEAL_TYPE_PERCENTAGE[$numberOfMeals][$meal->getType()])) {
                    $idealKcals = round(MealPlan::MEAL_TYPE_PERCENTAGE[$numberOfMeals][$meal->getType()] * (int) $masterMealPlan->getDesiredKcals());
                }
            }

            $products = $this->serializeMealProducts($meal, $locale);

            $image = $meal->getImage();

            if (!$image) {
                $recipe = $meal->getRecipe();
                $image = $recipe ? $recipe->getImage() : 'https://app.zenfitapp.com/images/meal_thumbnail.png';
            }

            $recipe = $meal->getRecipe();
            $meta = [];
            $types = [];
            if($recipe !== null) {
                $recipeMeta = $recipe->getRecipeMeta();
                $meta['lactose'] = $recipeMeta->getLactose();
                $meta['gluten'] = $recipeMeta->getGluten();
                $meta['nuts'] = $recipeMeta->getNuts();
                $meta['eggs'] = $recipeMeta->getEggs();
                $meta['pig'] = $recipeMeta->getPig();
                $meta['shellfish'] = $recipeMeta->getShellfish();
                $meta['fish'] = $recipeMeta->getFish();
                $meta['is_vegetarian'] = $recipeMeta->getIsVegetarian();
                $meta['is_vegan'] = $recipeMeta->getIsVegan();
                $meta['is_pescetarian'] = $recipeMeta->getIsPescetarian();

                $recipeTypes = $recipe->getTypes();
                foreach ($recipeTypes as $recipeType) {
                    $types[] = $recipeType->getType();
                }
            }
            $res[] = [
                'id' => $meal->getId(),
                'name' => $meal->getName(),
                'comment' => $meal->getComment(),
                'image' => $image,
                'order' => $meal->getOrder(),
                'type' => $meal->getType(),
                'locale' => $locale,
                'totals' => $meal->getTotals(),
                'recipe' => $recipe?->getId(),
                'cooking_time' => $recipe?->getCookingTime(),
                'recipe_meta' => $meta,
                'recipe_types' => $types,
                'ideal_kcals' => $idealKcals,
                'contains_alternatives' => $masterMealPlan->getContainsAlternatives(),
                'products' => $products,
                'desired_kcals' => $masterMealPlan->getDesiredKcals(),
                'macro_split' => $meal->getMacroSplit()
            ];
        }

        return $res;
    }

    public function transformParentMealPlans($mealPlans)
    {
        $newMealPlansArray = [];
        $totalAvgs = [];

        foreach ($mealPlans as $mealPlan) {
            $newMealPlansArray[$mealPlan['id']] = $mealPlan;

            if (!isset($totalAvgs[$mealPlan['id']])) {
                $totalAvgs[$mealPlan['id']] = [
                    'kcal' => 0,
                    'protein' => 0,
                    'carbohydrate' => 0,
                    'fat' => 0
                ];
            }

            foreach ($mealPlan['meals'] as $meals) {
                $totals = $meals['totals'];
                $totalAvgs[$mealPlan['id']]['kcal'] += $totals['kcal'];
                $totalAvgs[$mealPlan['id']]['protein'] += $totals['protein'];
                $totalAvgs[$mealPlan['id']]['carbohydrate'] += $totals['carbohydrate'];
                $totalAvgs[$mealPlan['id']]['fat'] += $totals['fat'];
            }
        }

        //set avg totals for each meal plan
        foreach ($totalAvgs as $mealPlan => $totalAvg) {
            $alternatives = count($newMealPlansArray[$mealPlan]['meals']);
            $newMealPlansArray[$mealPlan]['avg_totals'] = [
                'kcal' => $alternatives ? round($totalAvgs[$mealPlan]['kcal'] / $alternatives) : 0,
                'protein' => $alternatives ? round($totalAvgs[$mealPlan]['protein'] / $alternatives) : 0,
                'carbohydrate' => $alternatives ? round($totalAvgs[$mealPlan]['carbohydrate'] / $alternatives) : 0,
                'fat' => $alternatives ? round($totalAvgs[$mealPlan]['fat'] / $alternatives) : 0
            ];
        }

        return $newMealPlansArray;
    }

    private function serializeMealProducts($meal, $locale)
    {
        return array_map(function (MealPlanProduct $mealProduct) use ($locale) {
            $product = $mealProduct->getProduct();
            $weight = $mealProduct->getWeight();

            return [
                'id' => $mealProduct->getId(),
                'product' => (new MealProductTransformer())->transform($product, $locale),
                'order' => $mealProduct->getOrder(),
                'totalWeight' => $mealProduct->getTotalWeight(),
                'weight' => $weight ? [
                    'id' => $weight->getId(),
                    'name' => $weight->getName(),
                    'locale' => $weight->getLocale(),
                    'weight' => $weight->getWeight(),
                ] : null,
                'weightUnits' => $mealProduct->getWeightUnits(),
                'weights' => $product->weightList(),
            ];
        }, $meal->getProducts()->toArray());
    }

    private function planMacroSplit(MasterMealPlan $plan, $mealPlans)
    {
        $macroSplit = $plan->getMacroSplit();
        foreach ($mealPlans as $mealPlan) {
            if ($macroSplit != $mealPlan['macroSplit']) {
                return 0;
            }
        }
        return $macroSplit;
    }

    private function checkMealPlanKcalFit($mealPlans)
    {
        $threshold = 25;
        foreach ($mealPlans as $mealPlan) {
            foreach ($mealPlan['meals'] as $meal) {
                $diff = abs($meal['ideal_kcals'] - $meal['totals']['kcal']);
                if ($diff > $threshold) {
                    return false;
                }
            }
        }

        return true;
    }

    public function planAvgTotals($mealPlans)
    {
        $avgTotals = [
            'kcal' => 0,
            'protein' => 0,
            'carbohydrate' => 0,
            'fat' => 0
        ];

        foreach ($mealPlans as $mealPlan) {
            $avgTotals = [
                'kcal' => isset($mealPlan['avg_totals']) ? $avgTotals['kcal'] + $mealPlan['avg_totals']['kcal'] : null,
                'protein' => isset($mealPlan['avg_totals']) ? $avgTotals['protein'] + $mealPlan['avg_totals']['protein'] : null,
                'carbohydrate' => isset($mealPlan['avg_totals']) ? $avgTotals['carbohydrate'] + $mealPlan['avg_totals']['carbohydrate'] : null,
                'fat' => isset($mealPlan['avg_totals']) ? $avgTotals['fat'] + $mealPlan['avg_totals']['fat'] : null,
            ];
        }

        return $avgTotals;
    }

    public function idealTotals(MasterMealPlan $masterMealPlan, MealPlan $meal)
    {
        if (!$masterMealPlan->getContainsAlternatives()) {
            return;
        }

        $numberOfMeals = count($masterMealPlan->getMealPlansWhereParentIsNull());
        $idealTotals = [
            'kcal' => 0,
            'protein' => 0,
            'carbohydrate' => 0,
            'fat' => 0
        ];

        $percentWeight = $meal->getPercentWeight();
        $idealKcals = 0;
        if ($percentWeight !== null) {
            $idealKcals = round($masterMealPlan->getDesiredKcals() * (float) $percentWeight);
        } elseif (isset(MealPlan::MEAL_TYPE_PERCENTAGE[$numberOfMeals][$meal->getType()])) {
            $idealKcals = round(MealPlan::MEAL_TYPE_PERCENTAGE[$numberOfMeals][$meal->getType()] * (int) $masterMealPlan->getDesiredKcals());
        }

        if($masterMealPlan->getType() == MasterMealPlan::TYPE_CUSTOM_MACROS) {
            $macros = (array) $masterMealPlan->getParameterByKey('macros');
            $idealTotals = $this->recipeCustomGeneratorService->prepareRecipeMacros($macros, $numberOfMeals, $meal->getType(), $meal);
        } elseif ($meal->getMacroSplit()) {
            $idealTotals['protein'] = $idealKcals / 4 * MealPlan::MACRO_SPLIT[$meal->getMacroSplit()]['protein'];
            $idealTotals['carbohydrate'] = $idealKcals / 4 * MealPlan::MACRO_SPLIT[$meal->getMacroSplit()]['carbohydrate'];
            $idealTotals['fat'] = $idealKcals / 9 * MealPlan::MACRO_SPLIT[$meal->getMacroSplit()]['fat'];
        }

        $idealTotals['kcal'] = $idealKcals;

        return $idealTotals;
    }

    private function serializeMasterMealPlanMeta(MasterMealPlan $masterMealPlan)
    {
        $meta = $masterMealPlan->getMasterMealPlanMeta();

        if (!$meta) {
            return null;
        }

        return [
            'type' => $meta->getType(),
            'duration' => $meta->getDuration(),
        ];
    }

}
