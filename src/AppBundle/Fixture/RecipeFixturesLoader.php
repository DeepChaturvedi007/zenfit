<?php declare(strict_types=1);

namespace AppBundle\Fixture;

use AppBundle\Entity\Language;
use AppBundle\Entity\Recipe;
use AppBundle\Entity\RecipeMeta;
use AppBundle\Entity\RecipeType;
use AppBundle\Repository\RecipeRepository;

class RecipeFixturesLoader
{
    private RecipeRepository $recipeRepository;

    public function __construct(RecipeRepository $recipeRepository)
    {
        $this->recipeRepository = $recipeRepository;
    }

    public function __invoke(): void
    {
        foreach ($this->getData() as $item) {
            $existentRecipe = $this->recipeRepository->findOneBy(['excelId' => $item['excel_id']]);
            if ($existentRecipe !== null) {
                continue;
            }

            $this->createARecipeFromArray($item);
        }

        $this->recipeRepository->flush();
    }

    private function createARecipeFromArray(array $item): Recipe
    {
        $recipe = new Recipe($item['name'], $item['locale']);
        $recipe->setExcelId($item['excel_id']);
        foreach ($item['types'] as $typeId) {
            $recipeType = new RecipeType($recipe, $typeId);
            $recipe->addType($recipeType);
        }

        $recipeMeta = new RecipeMeta($recipe);
        $recipeMeta->setEggs($item['recipe_meta']['eggs']);
        $recipeMeta->setFish($item['recipe_meta']['fish']);
        $recipeMeta->setGluten($item['recipe_meta']['gluten']);
        $recipeMeta->setLactose($item['recipe_meta']['lactose']);
        $recipeMeta->setNuts($item['recipe_meta']['nuts']);
        $recipeMeta->setPig($item['recipe_meta']['pig']);
        $recipeMeta->setShellfish($item['recipe_meta']['shellfish']);
        $recipeMeta->setIsPescetarian($item['recipe_meta']['is_pescetarian']);
        $recipeMeta->setIsVegan($item['recipe_meta']['is_vegan']);
        $recipeMeta->setIsVegetarian($item['recipe_meta']['is_vegetarian']);
        $recipe->setRecipeMeta($recipeMeta);

        $recipe->setMacroSplit($item['macro_split']);
        $recipe->setImage($item['image']);
        $recipe->setComment($item['comment']);
        $recipe->setCookingTime($item['cooking_time']);
        $this->recipeRepository->persist($recipe);
        if ($item['parent'] !== null) {
            $recipe->setParent($this->createARecipeFromArray($item['parent']));
        }

        return $recipe;
    }

    /** @return array<int, mixed> */
    private function getData(): iterable
    {
        return [
            [
                'excel_id' => 132,
                'name' => 'Blueberry oatmeal',
                'types' => [
                    Recipe::TYPE_BREAKFAST,
                    Recipe::TYPE_MORNING_SNACK,
                    Recipe::TYPE_AFTERNOON_SNACK,
                    Recipe::TYPE_EVENING_SNACK,
                ],
                'locale' => Language::LOCALE_EN,
                'recipe_meta' => [
                    'lactose' => 1,
                    'gluten' => 1,
                    'nuts' => 1,
                    'eggs' => 0,
                    'pig' => 0,
                    'shellfish' => 0,
                    'fish' => 0,
                    'is_vegetarian' => 1,
                    'is_vegan' => 0,
                    'is_pescetarian' => 1,
                ],
                'macro_split' => 2,
                'image' => 'https://s3.eu-central-1.amazonaws.com/zenfit-images/recipes/132a.jpg',
                'comment' => '1. Mix oats with water and protein powder isolat (choose your favorite flavour).\n2. Cook in microwave 30 sec, stir, and repeat. (alternatively add sugar free sweetener)\n3. Add peanutbutter, stir and put blueberries on top. ',
                'cooking_time' => Recipe::TIME_FAST,
                'parent' => null,
            ],
            [
                'excel_id' => 133,
                'name' => 'Chicken thai dish',
                'types' => [
                    Recipe::TYPE_LUNCH,
                    Recipe::TYPE_DINNER,
                ],
                'locale' => Language::LOCALE_EN,
                'recipe_meta' => [
                    'lactose' => 0,
                    'gluten' => 0,
                    'nuts' => 1,
                    'eggs' => 0,
                    'pig' => 0,
                    'shellfish' => 0,
                    'fish' => 0,
                    'is_vegetarian' => 0,
                    'is_vegan' => 0,
                    'is_pescetarian' => 0,
                ],
                'macro_split' => 2,
                'image' => 'https://s3.eu-central-1.amazonaws.com/zenfit-images/recipes/173a.jpg',
                'comment' => '1. Prepare sliced pieces of chicken in a pan\n2. Add thai curry paste with coconut milk in the pan\n3. Add favorite vegetables\n4. Serve rice ',
                'cooking_time' => Recipe::TIME_MID_SLOW,
                'parent' => null,
            ],
            [
                'excel_id' => 134,
                'name' => 'Icelandic skyr with muesli and blueberries',
                'types' => [
                    Recipe::TYPE_MORNING_SNACK,
                    Recipe::TYPE_AFTERNOON_SNACK,
                    Recipe::TYPE_EVENING_SNACK,
                ],
                'locale' => Language::LOCALE_EN,
                'recipe_meta' => [
                    'lactose' => 1,
                    'gluten' => 1,
                    'nuts' => 1,
                    'eggs' => 0,
                    'pig' => 0,
                    'shellfish' => 0,
                    'fish' => 0,
                    'is_vegetarian' => 1,
                    'is_vegan' => 0,
                    'is_pescetarian' => 1,
                ],
                'macro_split' => 2,
                'image' => 'https://s3.eu-central-1.amazonaws.com/zenfit-images/recipes/220a.jpg',
                'comment' => '1. Add icelandic youghurt (skyr) or greek yoghurt in tall glass\n2. Mix muesli and peanutbutter\n3. Add blueberries on top',
                'cooking_time' => Recipe::TIME_FAST,
                'parent' => null,
            ],
            [
                'excel_id' => 135,
                'name' => 'Egg wrap with mushrooms',
                'types' => [
                    Recipe::TYPE_MORNING_SNACK,
                    Recipe::TYPE_AFTERNOON_SNACK,
                    Recipe::TYPE_EVENING_SNACK,
                ],
                'locale' => Language::LOCALE_EN,
                'recipe_meta' => [
                    'lactose' => 0,
                    'gluten' => 0,
                    'nuts' => 0,
                    'eggs' => 1,
                    'pig' => 0,
                    'shellfish' => 0,
                    'fish' => 0,
                    'is_vegetarian' => 1,
                    'is_vegan' => 0,
                    'is_pescetarian' => 1,
                ],
                'macro_split' => 2,
                'image' => 'https://s3.eu-central-1.amazonaws.com/zenfit-images/recipes/221a.jpg',
                'comment' => '1. Heat vegetables on pan until light brown.\n2. Add eggs.\n3. Heat on both sides',
                'cooking_time' => Recipe::TIME_FAST,
                'parent' => null,
            ],
            [
                'excel_id' => 136,
                'name' => 'Omelette with ham',
                'types' => [
                    Recipe::TYPE_BREAKFAST
                ],
                'locale' => Language::LOCALE_EN,
                'recipe_meta' => [
                    'lactose' => 1,
                    'gluten' => 1,
                    'nuts' => 0,
                    'eggs' => 1,
                    'pig' => 1,
                    'shellfish' => 0,
                    'fish' => 0,
                    'is_vegetarian' => 0,
                    'is_vegan' => 0,
                    'is_pescetarian' => 0,
                ],
                'macro_split' => 2,
                'image' => 'https://s3.eu-central-1.amazonaws.com/zenfit-images/recipes/133a.jpg',
                'comment' => '1. Scramble ham, eggs and spinach. (Frozen spinach are cooked first)\n2. Eat toasted bread on the side',
                'cooking_time' => Recipe::TIME_FAST,
                'parent' => null,
            ],
            [
                'excel_id' => 132,
                'name' => 'Havregr??d med bl??b??r',
                'types' => [
                    Recipe::TYPE_BREAKFAST,
                    Recipe::TYPE_LUNCH,
                    Recipe::TYPE_DINNER
                ],
                'locale' => Language::LOCALE_DK,
                'recipe_meta' => [
                    'lactose' => 1,
                    'gluten' => 1,
                    'nuts' => 1,
                    'eggs' => 1,
                    'pig' => 0,
                    'shellfish' => 0,
                    'fish' => 0,
                    'is_vegetarian' => 1,
                    'is_vegan' => 0,
                    'is_pescetarian' => 1,
                ],
                'macro_split' => 2,
                'image' => 'https://s3.eu-central-1.amazonaws.com/zenfit-images/recipes/132a.jpg',
                'comment' => '1. Bland havregryn med vand og proteinpulver (valgfri smag)\n2. Kog i micro-ovn i 30 sek. ad gangen, r??r, og gentag til det er en tyk gr??d. \n3. Tilf??j peanutbutter, r??r rundt, og pynt med bl??b??r (tilf??j evt s??demiddel)',
                'cooking_time' => Recipe::TIME_FAST,
                'parent' => null,
            ],
            [
                'excel_id' => 133,
                'name' => 'Omelet med skinke',
                'types' => [
                    Recipe::TYPE_BREAKFAST,
                    Recipe::TYPE_LUNCH,
                    Recipe::TYPE_DINNER
                ],
                'locale' => Language::LOCALE_DK,
                'recipe_meta' => [
                    'lactose' => 0,
                    'gluten' => 1,
                    'nuts' => 0,
                    'eggs' => 1,
                    'pig' => 1,
                    'shellfish' => 0,
                    'fish' => 0,
                    'is_vegetarian' => 0,
                    'is_vegan' => 0,
                    'is_pescetarian' => 0,
                ],
                'macro_split' => 2,
                'image' => 'https://s3.eu-central-1.amazonaws.com/zenfit-images/recipes/133a.jpg',
                'comment' => '1. Scramble  ??g, skinke og spinat sammen (Hvis spinaten er frossen , steges dette f??rst indtil opt??et)\n2. Toast br??d, og tilf??j scrambled eggs ovenp??',
                'cooking_time' => Recipe::TIME_FAST,
                'parent' => [
                    'excel_id' => 150,
                    'name' => 'Omelet med skinke',
                    'types' => [
                        Recipe::TYPE_BREAKFAST,
                        Recipe::TYPE_LUNCH,
                        Recipe::TYPE_DINNER
                    ],
                    'locale' => Language::LOCALE_DK,
                    'recipe_meta' => [
                        'lactose' => 1,
                        'gluten' => 1,
                        'nuts' => 0,
                        'eggs' => 1,
                        'pig' => 1,
                        'shellfish' => 0,
                        'fish' => 0,
                        'is_vegetarian' => 0,
                        'is_vegan' => 0,
                        'is_pescetarian' => 0,
                    ],
                    'macro_split' => 1,
                    'image' => 'https://s3.eu-central-1.amazonaws.com/zenfit-images/recipes/133a.jpg',
                    'comment' => '1. Scramble ??g, skinke og spinat sammen (Hvis spinaten er frossen steges dette f??rst indtil opt??et)\n2. Toast br??d, og tilf??j scrambled eggs ovenp??.\n3. Evt top med lidt sukkerfri ketchup og salt',
                    'cooking_time' => Recipe::TIME_FAST,
                    'parent' => null,
                ],
            ],
            [
                'excel_id' => 134,
                'name' => 'Hurtig spansk omelet',
                'types' => [
                    Recipe::TYPE_BREAKFAST,
                    Recipe::TYPE_LUNCH,
                    Recipe::TYPE_DINNER
                ],
                'locale' => Language::LOCALE_DK,
                'recipe_meta' => [
                    'lactose' => 1,
                    'gluten' => 0,
                    'nuts' => 0,
                    'eggs' => 1,
                    'pig' => 0,
                    'shellfish' => 0,
                    'fish' => 0,
                    'is_vegetarian' => 0,
                    'is_vegan' => 0,
                    'is_pescetarian' => 0,
                ],
                'macro_split' => 2,
                'image' => 'https://s3.eu-central-1.amazonaws.com/zenfit-images/recipes/134a.jpg',
                'comment' => '1. Snit fine skiver af kartofler, og sk??r l??g og asparages.\n2. Brun p?? panden, mens du forbereder ??g og kylling.\n3. Skru ned p?? lavt blus og tilf??j ??g og kylling. R??r kortvarigt\n4. Lad st?? og f??rdigg??r, indtil du kan vende siden (brug en stor tallerken til at vende omelet med)',
                'cooking_time' => Recipe::TIME_MID,
                'parent' => null,
            ],
            [
                'excel_id' => 135,
                'name' => 'Hytteost proteinpandekager',
                'types' => [
                    Recipe::TYPE_BREAKFAST,
                    Recipe::TYPE_LUNCH,
                    Recipe::TYPE_DINNER
                ],
                'locale' => Language::LOCALE_DK,
                'recipe_meta' => [
                    'lactose' => 1,
                    'gluten' => 1,
                    'nuts' => 0,
                    'eggs' => 0,
                    'pig' => 0,
                    'shellfish' => 0,
                    'fish' => 0,
                    'is_vegetarian' => 0,
                    'is_vegan' => 0,
                    'is_pescetarian' => 0,
                ],
                'macro_split' => 2,
                'image' => 'https://s3.eu-central-1.amazonaws.com/zenfit-images/recipes/135a.jpg',
                'comment' => '1. Blend alle ingredienter, og tilf??j lidt s??demiddel.\n2. Steg p?? stegepande\n3. Serv??r med lidt sukkerfri syltet??j eller lidt b??r',
                'cooking_time' => Recipe::TIME_MID,
                'parent' => null,
            ],
            [
                'excel_id' => 136,
                'name' => 'Overnight oats',
                'types' => [
                    Recipe::TYPE_BREAKFAST,
                    Recipe::TYPE_LUNCH,
                    Recipe::TYPE_DINNER
                ],
                'locale' => Language::LOCALE_DK,
                'recipe_meta' => [
                    'lactose' => 1,
                    'gluten' => 1,
                    'nuts' => 0,
                    'eggs' => 0,
                    'pig' => 0,
                    'shellfish' => 0,
                    'fish' => 0,
                    'is_vegetarian' => 1,
                    'is_vegan' => 0,
                    'is_pescetarian' => 1,
                ],
                'macro_split' => 2,
                'image' => 'https://s3.eu-central-1.amazonaws.com/zenfit-images/recipes/136a.jpg',
                'comment' => '1. Tilf??j alle ingredienser i en plastikb??tte og r??r rundt til det er mixet helt sammen\n2. Lad b??tten st?? i k??leskabet natten over\n3. Spises til morgenmad',
                'cooking_time' => Recipe::TIME_FAST,
                'parent' => null,
            ],
        ];
    }
}
