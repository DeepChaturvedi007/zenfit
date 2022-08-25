<?php declare(strict_types=1);

namespace AppBundle\Fixture;

use AppBundle\Entity\MealProduct;
use AppBundle\Entity\Recipe;
use AppBundle\Entity\RecipeProduct;
use AppBundle\Repository\MealProductRepository;
use AppBundle\Repository\MealProductWeightRepository;
use AppBundle\Repository\RecipeProductRepository;
use AppBundle\Repository\RecipeRepository;

class RecipeProductFixturesLoader
{
    private RecipeProductRepository $recipeProductRepository;
    private RecipeRepository $recipeRepository;
    private MealProductWeightRepository $productWeightRepository;
    private MealProductRepository $mealProductRepository;

    public function __construct(
        RecipeProductRepository $recipeProductRepository,
        RecipeRepository $recipeRepository,
        MealProductRepository $mealProductRepository,
        MealProductWeightRepository $productWeightRepository
    )
    {
        $this->productWeightRepository = $productWeightRepository;
        $this->recipeRepository = $recipeRepository;
        $this->recipeProductRepository = $recipeProductRepository;
        $this->mealProductRepository = $mealProductRepository;
    }

    public function __invoke(): void
    {
        foreach ($this->getData() as $item) {
            /** @var Recipe $recipe */
            $recipe = $this->recipeRepository->findOneBy(['name' => $item[0]]);
            /** @var MealProduct $product */
            $product = $this->mealProductRepository->findOneBy(['name' => $item[1]]);

            $productWeight = null;
            if ($item[2] !== null) {
                $productWeight = $this->productWeightRepository->findOneBy(['product' => $product, 'name' => $item[2], 'locale' => $item[3]]);
            }

            $object = $this->recipeProductRepository->findOneBy(['recipe' => $recipe, 'product' => $product, 'weight' => $productWeight]);

            if ($object !== null) {
                continue;
            }

            $object = new RecipeProduct($recipe, $product);
            $object->setWeight($productWeight);
            $object->setTotalWeight($item[4]);
            $object->setWeightUnits($item[5]);
            $object->setOrder($item[6]);

            $this->recipeProductRepository->persist($object);
        }

        $this->recipeProductRepository->flush();
    }

    private function getData(): array
    {
        //0 recipe_name, 1 meal_product_name, 2 product_weight_name, 3 product_weight_locale, 4 total_weight, 5 weight_units, 6 order
        return
            [
                ['Blueberry oatmeal', 'Oats', 'oz.', 'en', 42, 1.50, 1],
                ['Blueberry oatmeal', 'Protein powder', 'tsp', 'en', 39, 6.50, 2],
                ['Blueberry oatmeal', 'Blueberry', 'pcs', 'en', 40, 20.00, 3],
                ['Blueberry oatmeal', 'Peanut butter. low in sugar', 'teaspoon', 'en', 6, 1.00, 4],
                ['Chicken thai dish', 'Chicken filet. natural', 'ounce', 'en', 126, 4.50, 1],
                ['Chicken thai dish', 'Rice. white. long-grain. dry', 'ounce', 'en', 28, 1.00, 2],
                ['Chicken thai dish', 'Coconut milk. light. canned', 'tablespoon', 'en', 60, 4.00, 3],
                ['Chicken thai dish', 'Mixed greens', 'oz.', 'en', 112, 4.00, 4],
                ['Icelandic skyr with muesli and blueberries', 'Icelandic yoghurt. skyr. reduced sugar', 'ounce', 'en', 294, 10.50, 1],
                ['Icelandic skyr with muesli and blueberries', 'BLUEBERRIES', 'berries', 'en', 40, 20.00, 2],
                ['Icelandic skyr with muesli and blueberries', 'Muesli. low in fat & sugar', 'ounce', 'en', 42, 1.50, 3],
                ['Icelandic skyr with muesli and blueberries', 'Peanut butter. low in sugar', 'teaspoon', 'en', 15, 2.50, 4],
                ['Egg wrap with mushrooms', 'Egg', 'medium', 'en', 55, 1.00, 1],
                ['Egg wrap with mushrooms', 'Egg whites', 'reg. size', 'en', 150, 5.00, 2],
                ['Egg wrap with mushrooms', 'Mushroom', 'cup', 'en', 70, 1.00, 3],
                ['Egg wrap with mushrooms', 'Bell pepper. sweet. green', 'pcs', 'en', 95, 0.50, 4],
                ['Egg wrap with mushrooms', 'Bread. white', 'reg. slice', 'en', 20, 0.50, 5],
                ['Egg wrap with mushrooms', 'Tomato', NULL, NULL, 150, 2.00, 6],
                ['Omelette with ham', 'Egg', 'medium', 'en', 55, 1.00, 1],
                ['Omelette with ham', 'Egg white', 'pcs', 'en', 175, 5.00, 2],
                ['Omelette with ham', 'Spinach', 'oz.', 'en', 98, 3.50, 3],
                ['Omelette with ham', 'Pork. ham. boiled. sliced', 'oz.', 'en', 28, 1.00, 4],
                ['Omelette with ham', 'Bread. white', 'oz.', 'en', 70, 2.50, 5],
                ['Havregrød med blåbær', 'Oats', NULL, NULL, 40, 0.00, 1],
                ['Havregrød med blåbær', 'Protein powder', NULL, NULL, 35, 0.00, 2],
                ['Havregrød med blåbær', 'Blueberry', NULL, NULL, 40, 0.00, 3],
                ['Havregrød med blåbær', 'Egg whites. pasteurized', 'stk', 'da_DK', 30, 1.00, 4],
                ['Havregrød med blåbær', 'Peanut butter. low in sugar', 'tsk', 'da_DK', 6, 1.00, 5],
                ['Omelet med skinke', 'Egg', 'lille', 'da_DK', 40, 1.00, 1],
                ['Omelet med skinke', 'Egg whites. pasteurized', 'stk', 'da_DK', 120, 4.00, 2],
                ['Omelet med skinke', 'Spinach', NULL, NULL, 50, 0.00, 3],
                ['Omelet med skinke', 'Onion', 'lille', 'da_DK', 55, 1.00, 4],
                ['Omelet med skinke', 'Pork. ham. boiled. sliced', 'skive', 'da_DK', 90, 9.00, 5],
                ['Omelet med skinke', 'Rye bread. dark. wholemeal', 'skive', 'da_DK', 67, 1.50, 6],
                ['Hurtig spansk omelet', 'Egg', 'medium', 'da_DK', 55, 1.00, 1],
                ['Hurtig spansk omelet', 'Egg whites', 'stk', 'da_DK', 90, 3.00, 2],
                ['Hurtig spansk omelet', 'Spring onion', 'normal størrelse', 'da_DK', 19, 1.00, 3],
                ['Hurtig spansk omelet', 'Potato. raw', NULL, NULL, 175, 0.00, 4],
                ['Hurtig spansk omelet', 'Chicken. cold cut', 'skive', 'da_DK', 84, 6.00, 5],
                ['Hurtig spansk omelet', 'Asparagus. green', 'stor', 'da_DK', 20, 2.00, 6],
                ['Hytteost proteinpandekager ', 'Oats', NULL, NULL, 40, 0.00, 1],
                ['Hytteost proteinpandekager ', 'Cottage cheese. 1.5%', NULL, NULL, 45, 0.00, 2],
                ['Hytteost proteinpandekager ', 'Protein powder', 'tsk', 'da_DK', 24, 4.00, 3],
                ['Hytteost proteinpandekager ', 'Banana', 'mellem', 'da_DK', 52, 0.50, 4],
                ['Hytteost proteinpandekager ', 'Egg', 'medium', 'da_DK', 55, 1.00, 5],
                ['Hytteost proteinpandekager ', 'Egg whites', 'stk', 'da_DK', 60, 3.00, 6],
                ['Overnight oats', 'Oats', NULL, NULL, 35, 0.00, 1],
                ['Overnight oats', 'Milk. skimmed. 0.5 % fat', NULL, NULL, 50, 0.00, 2],
                ['Overnight oats', 'CHIA SEEDS. DRIED', NULL, NULL, 15, 0.00, 3],
                ['Overnight oats', 'Blueberry', NULL, NULL, 20, 0.00, 4],
                ['Overnight oats', 'Skyr. 0.2%. Vanilla', NULL, NULL, 150, 0.00, 5],
                ['Overnight oats', 'Protein powder', NULL, NULL, 25, 0.00, 6],
                ['Omelet med skinke', 'Egg', 'medium', 'da_DK', 55, 1.00, 1],
                ['Omelet med skinke', 'Egg whites', 'stk', 'da_DK', 90, 3.00, 2],
                ['Omelet med skinke', 'Spinach', NULL, NULL, 50, 0.00, 3],
                ['Omelet med skinke', 'Onion', 'lille', 'da_DK', 55, 1.00, 4],
                ['Omelet med skinke', 'Pork. ham. boiled. sliced', 'skive', 'da_DK', 100, 10.00, 5],
                ['Omelet med skinke', 'Bread. white', 'normal skive', 'da_DK', 160, 4.00, 6],
            ];
    }
}
