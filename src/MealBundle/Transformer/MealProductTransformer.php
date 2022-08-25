<?php

namespace MealBundle\Transformer;

use League\Fractal\TransformerAbstract;
use AppBundle\Entity\MealProduct;
use AppBundle\Entity\Language;
use Stringy\StaticStringy;

class MealProductTransformer extends TransformerAbstract
{
    /**
     * @param MealProduct $product
     * @param $locale
     * @return array
     */
    public function transform(MealProduct $product, $locale)
    {
        $name = $product
            ->getMealProductLanguageByLocale($locale)
            ->getName();

        //in german the upper and lower case words are important
        //thats why we keep original format
        if ($locale != Language::LOCALE_DE) {
            $name = StaticStringy::titleize($name);
        }

        $brand = $product->getMealProductLanguageByLocale($locale)
            ? $product->getMealProductLanguageByLocale($locale)->getBrand()
            : $product->getBrand();

        $recommended = ($product->getGlutenFreeAlternative() or $product->getLactoseFreeAlternative()) ? true : false;

        return [
            'id' => $product->getId(),
            'name' => $name,
            'brand' => $brand,
            'kcal' => $product->getKcal(),
            'kj' => $product->getKj(),
            'fat' => (float) $product->getFat(),
            'protein' => (float) $product->getProtein(),
            'carbohydrates' => (float) $product->getCarbohydrates(),
            'weights' => $product->weightList(),
            'locale' => $locale,
            'recommended' => $recommended,
            'meta' => $product->serializedMealProductMeta()
        ];
    }
}
