<?php declare(strict_types=1);

namespace AppBundle\Entity;

use App\EntityIdTrait;

class RecipeProduct
{
    use EntityIdTrait;

    private Recipe $recipe;
    private MealProduct $product;
    private int $totalWeight = 0;
    private int $order = 0;
    private ?MealProductWeight $weight = null;
    private float $weightUnits = 0;
    private bool $tweak = false;

    public function __construct(Recipe $recipe, MealProduct $mealProduct)
    {
        $this->recipe = $recipe;
        $this->product = $mealProduct;
    }

    public function getRecipe(): Recipe
    {
        return $this->recipe;
    }

    public function setRecipe(Recipe $recipe): self
    {
        $this->recipe = $recipe;
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

    /** @return array{kcal: int, protein: float, carbohydrates: float, fat: float}} */
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

    public function setWeightUnits(float $weightUnits): self
    {
        $this->weightUnits = $weightUnits;
        return $this;
    }

    public function setTweak(bool $tweak): self
    {
        $this->tweak = $tweak;

        return $this;
    }

    public function getTweak(): bool
    {
        return $this->tweak;
    }
}
