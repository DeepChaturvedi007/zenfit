<?php

namespace AdminBundle\Transformer;

use AppBundle\Entity\MealProduct;
use Illuminate\Support\Collection;
use League\Fractal\TransformerAbstract;

class MealProductTransformer extends TransformerAbstract
{
    private ?Collection $entities;

    public function __construct(Collection $entities = null)
    {
        $this->entities = $entities;
    }

    /** @return array<mixed> */
    public function transform(MealProduct $entity): array
    {
        $weights = [];
        foreach ($entity->getWeights() as $weight) {
            $weights[$weight->getLocale()] = [
                'id' => $weight->getId(),
                'name' => $weight->getName(),
                'weight' => $weight->getWeight(),
                'locale' => $weight->getLocale(),
            ];
        }

        $names = [];
        foreach ($entity->getMealProductLanguages() as $name) {
            $locale = $name->getLanguage()->getLocale();
            $names[$locale] = [
                'id' => $name->getId(),
                'name' => $name->getName(),
                'locale' => $locale
            ];
        }
        return [
            'id' => $entity->getId(),
            'name' => $entity->getName(),
            'nameDanish' => $entity->getNameDanish(),
            'allowSplit' => $entity->getAllowSplit(),
            'alcohol' => $entity->getAlcohol(),
            'addedSugars' => $entity->getAddedSugars(),
            'brand' => $entity->getBrand(),
            'carbohydrates' => $entity->getCarbohydrates(),
            'cholesterol' => $entity->getCholesterol(),
            'deleted' => $entity->getDeleted(),
            'excelId' => $entity->getExcelId(),
            'fat' => $entity->getFat(),
            'fiber' => $entity->getFiber(),
            'kcal' => $entity->getKcal(),
            'kj' => $entity->getKj(),
            'label' => $entity->getLabel(),
            'monoUnsaturatedFat' => $entity->getMonoUnsaturatedFat(),
            'polyUnsaturatedFat' => $entity->getPolyUnsaturatedFat(),
            'protein' => $entity->getProtein(),
            'saturatedFat' => $entity->getSaturatedFat(),
            'names' => $names,
            'amounts' => $weights
        ];
    }

    /**
     * @return array
     */
    public function getTransformedCollection()
    {
        if ($this->entities === null) {
            throw new \RuntimeException('$this->entities is null');
        }
        return $this->entities->map(function ($item) {
            /** @var MealProduct $item */
            return $this->transform($item);
        })->toArray();
    }
}
