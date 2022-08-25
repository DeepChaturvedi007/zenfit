<?php declare(strict_types=1);

namespace MealBundle\Transformer;

use League\Fractal\TransformerAbstract;
use AppBundle\Repository\RecipeProductRepository;
use AppBundle\Entity\RecipeProduct;

class RecipeTransformer extends TransformerAbstract
{
    static $preferences = [
        'lactose' => 'avoidLactose',
        'gluten' => 'avoidGluten',
        'nuts' => 'avoidNuts',
        'eggs' => 'avoidEggs',
        'pig' => 'avoidPig',
        'shellfish' => 'avoidShellfish',
        'fish' => 'avoidFish',
        'isVegetarian' => 'isVegetarian',
        'isVegan' => 'isVegan',
        'isPescetarian' => 'isPescetarian',
    ];

    private RecipeProductRepository $recipeProductRepository;

    public function __construct(RecipeProductRepository $recipeProductRepository)
    {
        $this->recipeProductRepository = $recipeProductRepository;
    }

    /** @return array<mixed> */
    public function transform(array $recipe, string $locale, ?string $lastUsed): array
    {
        $data = collect($recipe)
            ->only(['id', 'name', 'type', 'image', 'cookingTime', 'macroSplit', 'favorite', 'createdAt']);

        $data['lastUsed'] = $lastUsed;

        $preferences = collect($recipe)
            ->only(array_keys(static::$preferences))
            ->filter(function ($value) {
                return $value;
            })
            ->map(function ($value, $key) {
                return static::$preferences[$key];
            })
            ->values();

        $data->put('foodPreferences', $preferences->toArray());
        $data->put('canHitMacros', true);

        //get ingredients in recipe
        $ingredients = $this
            ->recipeProductRepository
            ->getByRecipeIds([$recipe['id']]);

        $ingredients = collect($ingredients)
            ->map(function(RecipeProduct $recipeProduct) use ($locale) {
                return [
                    'id' => $recipeProduct->getProduct()->getId(),
                    'name' => $recipeProduct->getProduct()->getMealProductLanguageByLocale($locale)->getName()
                ];
            });

        $data->put('ingredients', $ingredients->toArray());

        return $data->toArray();
    }
}
