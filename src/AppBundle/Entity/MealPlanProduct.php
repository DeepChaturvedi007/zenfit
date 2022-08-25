<?php declare(strict_types=1);

namespace AppBundle\Entity;

class MealPlanProduct
{
    private ?int $id = null;
    private ?MealPlan $plan = null;
    private MealProduct $product;
    private int $totalWeight = 0;
    private int $order = 0;
    private ?MealProductWeight $weight = null;
    private float $weightUnits = 0;

    public function __construct(MealProduct $mealProduct)
    {
        $this->product = $mealProduct;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPlan(): ?MealPlan
    {
        return $this->plan;
    }

    public function setPlan(?MealPlan $plan = null): self
    {
        $this->plan = $plan;
        return $this;
    }

    public function getProduct(): MealProduct
    {
        return $this->product;
    }

    public function setProduct(MealProduct $product): self
    {
        $this->product = $product;
        return $this;
    }

    public function getTotalWeight(): int
    {
        return $this->totalWeight;
    }

    public function setTotalWeight(int $totalWeight): self
    {
        $this->totalWeight = $totalWeight;
        return $this;
    }

    public function getOrder(): int
    {
        return $this->order;
    }

    public function setOrder(int $order): self
    {
        $this->order = $order;
        return $this;
    }

    public function getWeight(): ?MealProductWeight
    {
        return $this->weight;
    }

    public function setWeight(?MealProductWeight $weight): self
    {
        $this->weight = $weight;

        return $this;
    }

    public function getTotalKcal(): float
    {
        return (float) ($this->getProduct()->getKcal() * $this->getTotalWeight()) / 100;
    }

    /** @return array<mixed> */
    public function getAmounts(): array
    {
        $product = $this->getProduct();

        return [
            'kcal' => $product->getKcal(),
            'protein' => $product->getProtein(),
            'carbohydrates' => $product->getCarbohydrates(),
            'fat' => $product->getFat(),
        ];
    }

    public function getWeightUnits(): float
    {
        return (float) $this->weightUnits;
    }

    public function setWeightUnits(float|int $weightUnits): self
    {
        $this->weightUnits = (float) $weightUnits;
        return $this;
    }
}
