<?php declare(strict_types=1);

namespace AppBundle\Entity;

use App\EntityIdTrait;

class MealProductMeta
{
    use EntityIdTrait;

    private bool $lactose = false;
    private bool $gluten = false;
    private bool $nuts = false;
    private bool $eggs = false;
    private bool $pig = false;
    private bool $shellfish = false;
    private bool $fish = false;
    private bool $notVegetarian = false;
    private bool $notVegan = false;
    private bool $notPescetarian = false;
    private MealProduct $mealProduct;

    public function __construct(MealProduct $mealProduct)
    {
        $this->mealProduct = $mealProduct;
    }

    /** @return array<string, mixed> */
    public function serialize(): array
    {
        return [
            'lactose' => $this->getLactose(),
            'gluten' => $this->getGluten(),
            'nuts' => $this->getNuts(),
            'eggs' => $this->getEggs(),
            'pig' => $this->getPig(),
            'shellfish' => $this->getShellfish(),
            'fish' => $this->getFish(),
            'notVegetarian' => $this->getNotVegetarian(),
            'notVegan' => $this->getNotVegan(),
            'notPescetarian' => $this->getNotPescetarian()
        ];
    }

    public function setLactose(bool $lactose): self
    {
        $this->lactose = $lactose;
        return $this;
    }

    public function getLactose(): bool
    {
        return $this->lactose;
    }

    public function setGluten(bool $gluten): self
    {
        $this->gluten = $gluten;
        return $this;
    }

    public function getGluten(): bool
    {
        return $this->gluten;
    }

    public function setNuts(bool $nuts): self
    {
        $this->nuts = $nuts;
        return $this;
    }

    public function getNuts(): bool
    {
        return $this->nuts;
    }

    public function setEggs(bool $eggs): self
    {
        $this->eggs = $eggs;
        return $this;
    }

    public function getEggs(): bool
    {
        return $this->eggs;
    }

    public function setPig(bool $pig): self
    {
        $this->pig = $pig;
        return $this;
    }

    public function getPig(): bool
    {
        return $this->pig;
    }

    public function setShellfish(bool $shellfish): self
    {
        $this->shellfish = $shellfish;
        return $this;
    }

    public function getShellfish(): bool
    {
        return $this->shellfish;
    }

    public function setFish(bool $fish): self
    {
        $this->fish = $fish;
        return $this;
    }

    public function getFish(): bool
    {
        return $this->fish;
    }

    public function setNotVegetarian(bool $notVegetarian): self
    {
        $this->notVegetarian = $notVegetarian;
        return $this;
    }

    public function getNotVegetarian(): bool
    {
        return $this->notVegetarian;
    }

    public function setNotVegan(bool $notVegan): self
    {
        $this->notVegan = $notVegan;
        return $this;
    }

    public function getNotVegan(): bool
    {
        return $this->notVegan;
    }

    public function setNotPescetarian(bool $notPescetarian): self
    {
        $this->notPescetarian = $notPescetarian;
        return $this;
    }

    public function getNotPescetarian(): bool
    {
        return $this->notPescetarian;
    }

    public function setMealProduct(\AppBundle\Entity\MealProduct $mealProduct): self
    {
        $this->mealProduct = $mealProduct;
        return $this;
    }

    public function getMealProduct(): MealProduct
    {
        return $this->mealProduct;
    }
}
