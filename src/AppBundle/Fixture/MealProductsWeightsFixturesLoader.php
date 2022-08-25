<?php declare(strict_types=1);

namespace AppBundle\Fixture;

use AppBundle\Entity\MealProductWeight;
use AppBundle\Repository\MealProductRepository;
use AppBundle\Repository\MealProductWeightRepository;

class MealProductsWeightsFixturesLoader
{
    private MealProductWeightRepository $mealProductWeightRepository;
    private MealProductRepository $mealProductRepository;

    public function __construct(
        MealProductWeightRepository $mealProductWeightRepository,
        MealProductRepository $mealProductRepository
    ) {
        $this->mealProductWeightRepository = $mealProductWeightRepository;
        $this->mealProductRepository = $mealProductRepository;
    }

    public function __invoke(): void
    {
        foreach ($this->getData() as $item) {
            $product = $this->mealProductRepository->findOneBy(['name' => $item[1]]);
            if ($product === null) {
                throw new \RuntimeException();
            }

            $object = $this->mealProductWeightRepository->findOneBy(['name' => $item[0], 'product' => $product, 'locale' => $item[3]]);
            if ($object !== null) {
                continue;
            }

            $productWeight = new MealProductWeight();
            $productWeight->setName($item[0]);
            $productWeight->setWeight($item[2]);
            $productWeight->setLocale($item[3]);

            $productWeight->setProduct($product);

            $this->mealProductWeightRepository->persist($productWeight);
        }

        $this->mealProductWeightRepository->flush();
    }

    private function getData(): array
    {
        return
            [
                ['reg. size', 'Egg whites', 30.00, 'en'],
                ['stk', 'Egg whites', 30.00, 'da_DK'],
                ['berries', 'BLUEBERRIES', 2.00, 'en'],
                ['medium', 'Egg', 55.00, 'en'],
                ['lille', 'Egg', 40.00, 'da_DK'],
                ['medium', 'Egg', 55.00, 'da_DK'],
                ['pcs', 'Egg white', 35.00, 'en'],
                ['ounce', 'Icelandic yoghurt. skyr. reduced sugar', 28.00, 'en'],
                ['stk', 'Egg whites. pasteurized', 30.00, 'da_DK'],
                ['ounce', 'Chicken filet. natural', 28.00, 'en'],
                ['oz.', 'Pork. ham. boiled. sliced', 28.00, 'en'],
                ['stor', 'Asparagus. green', 20.00, 'da_DK'],
                ['mellem', 'Banana', 105.00, 'da_DK'],
                ['skive', 'Chicken. cold cut', 14.00, 'da_DK'],
                ['skive', 'Rye bread. dark. wholemeal', 45.00, 'da_DK'],
                ['normal størrelse', 'Spring onion', 19.00, 'da_DK'],
                ['normal skive', 'Bread. white', 40.00, 'da_DK'],
                ['tsk', 'Peanut butter. low in sugar', 6.00, 'da_DK'],
                ['lille', 'Onion', 55.00, 'da_DK'],
                ['skive', 'Pork. ham. boiled. sliced', 10.00, 'da_DK'],
                ['oz.', 'Mixed greens', 28.00, 'en'],
                ['oz.', 'Oats', 28.00, 'en'],
                ['pcs', 'Blueberry', 2.00, 'en'],
                ['teaspoon', 'Peanut butter. low in sugar', 6.00, 'en'],
                ['ounce', 'Muesli. low in fat & sugar', 28.00, 'en'],
                ['tablespoon', 'Coconut milk. light. canned', 15.00, 'en'],
                ['ounce', 'Rice. white. long-grain. dry', 28.00, 'en'],
                ['cup', 'Mushroom', 70.00, 'en'],
                ['oz.', 'Bread. white', 28.00, 'en'],
                ['pcs', 'Bell pepper. sweet. green', 190.00, 'en'],
                ['oz.', 'Spinach', 28.00, 'en'],
                ['reg. slice', 'Bread. white', 40.00, 'en'],
                ['tsp', 'Protein powder', 6.00, 'en'],
                ['tsk', 'Protein powder', 6.00, 'da_DK'],
            ];
    }
}
