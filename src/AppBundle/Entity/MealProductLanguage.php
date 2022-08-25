<?php declare(strict_types=1);

namespace AppBundle\Entity;

use App\EntityIdTrait;

class MealProductLanguage
{
    use EntityIdTrait;

    private string $name;
    private ?string $brand = null;
    private Language $language;
    private bool $deleted = false;
    private MealProduct $mealProduct;

    public function __construct(string $name, Language $language, MealProduct $mealProduct)
    {
        $this->name = $name;
        $this->language = $language;
        $this->mealProduct = $mealProduct;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setLanguage(Language $language): self
    {
        $this->language = $language;

        return $this;
    }

    public function getLanguage(): Language
    {
        return $this->language;
    }

    public function setMealProduct(MealProduct $mealProduct): self
    {
        $this->mealProduct = $mealProduct;

        return $this;
    }

    public function getMealProduct(): MealProduct
    {
        return $this->mealProduct;
    }

    public function setDeleted(bool $deleted): self
    {
        $this->deleted = $deleted;

        return $this;
    }

    public function getDeleted(): bool
    {
        return $this->deleted;
    }

    public function setBrand(?string $brand): self
    {
        $this->brand = $brand;

        return $this;
    }

    public function getBrand(): ?string
    {
        return $this->brand;
    }
}
