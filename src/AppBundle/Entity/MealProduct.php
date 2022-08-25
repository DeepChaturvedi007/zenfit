<?php declare(strict_types=1);

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class MealProduct
{
    private ?int $id = null;
    private string $name;
    private ?string $nameDanish = null;
    private ?string $brand = null;
    private int $kcal = 0;
    private int $kj = 0;
    private float $protein = 0;
    private float $fat = 0;
    private float $saturatedFat = 0;
    private float $monoUnsaturatedFat = 0;
    private float $polyUnsaturatedFat = 0;
    private float $carbohydrates = 0;
    private float $addedSugars = 0;
    private float $fiber = 0;
    private float $alcohol = 0;
    private float $cholesterol = 0;
    private ?User $user = null;
    private ?MealProduct $glutenFreeAlternative = null;
    private ?MealProduct $lactoseFreeAlternative = null;
    /** @var Collection<int, MealProductWeight> */
    private Collection $weights;

    public function __construct(string $name)
    {
        $this->name = $name;
        $this->weights = new ArrayCollection();
        $this->mealProductLanguages = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getBrand(): ?string
    {
        return $this->brand;
    }

    public function setBrand(?string $value): self
    {
        $this->brand = $value;
        return $this;
    }

    public function getKcal(): int
    {
        return $this->kcal;
    }

    public function setKcal(int $value): self
    {
        $this->kcal = $value;
        return $this;
    }

    public function getKj(): int
    {
        return $this->kj;
    }

    public function setKj(int $value): self
    {
        $this->kj = $value;
        return $this;
    }

    public function getProtein(): float
    {
        return $this->protein;
    }

    public function setProtein(string|float|int $value): self
    {
        $this->protein = (float) $value;
        return $this;
    }

    public function getFat(): float
    {
        return $this->fat;
    }

    public function setFat(string|int|float $value): self
    {
        $this->fat = (float) $value;
        return $this;
    }

    public function getSaturatedFat(): float
    {
        return $this->saturatedFat;
    }

    public function setSaturatedFats(string|float|int $value): self
    {
        $this->saturatedFat = (float) $value;
        return $this;
    }

    public function getMonoUnsaturatedFat(): float
    {
        return $this->monoUnsaturatedFat;
    }

    public function setMonoUnsaturatedFat(string|int|float $value): self
    {
        $this->monoUnsaturatedFat = (float) $value;
        return $this;
    }

    public function getPolyUnsaturatedFat(): float
    {
        return $this->polyUnsaturatedFat;
    }

    public function setPolyUnsaturatedFat(string|int|float $value): self
    {
        $this->polyUnsaturatedFat = (float) $value;
        return $this;
    }

    public function getCarbohydrates(): float
    {
        return $this->carbohydrates;
    }

    public function setCarbohydrates(string|float|int $value): self
    {
        $this->carbohydrates = (float) $value;
        return $this;
    }

    public function getAddedSugars(): float
    {
        return $this->addedSugars;
    }

    public function setAddedSugars(string|float|int $value): self
    {
        $this->addedSugars = (float) $value;
        return $this;
    }

    public function getFiber(): float
    {
        return $this->fiber;
    }

    public function setFiber(string|float|int $value): self
    {
        $this->fiber = (float) $value;
        return $this;
    }

    public function getAlcohol(): float
    {
        return $this->alcohol;
    }

    public function setAlcohol(string|float|int $value): self
    {
        $this->alcohol = (float) $value;
        return $this;
    }

    public function getNameDanish(): ?string
    {
        return $this->nameDanish;
    }

    /**
     * @param string $nameDanish
     * @return MealProduct
     */
    public function setNameDanish($nameDanish): self
    {
        $this->nameDanish = $nameDanish;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getLactoseFreeAlternative(): ?MealProduct
    {
        return $this->lactoseFreeAlternative;
    }

    public function setLactoseFreeAlternative(?MealProduct $lactoseFreeAlternative): self
    {
        $this->lactoseFreeAlternative = $lactoseFreeAlternative;
        return $this;
    }

    public function getGlutenFreeAlternative(): ?MealProduct
    {
        return $this->glutenFreeAlternative;
    }

    public function setGlutenFreeAlternative(?MealProduct $glutenFreeAlternative): self
    {
        $this->glutenFreeAlternative = $glutenFreeAlternative;
        return $this;
    }

    /** @return array<mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'name_danish' => $this->getNameDanish(),
            'protein' => $this->getProtein(),
            'brand' => $this->getBrand(),
            'carbohydrates' => $this->getCarbohydrates(),
            'fat' => $this->getFat(),
            'kcal' => $this->getKcal(),
            'kj' => $this->getKj(),
        ];
    }

    public function setSaturatedFat(string|float|int $saturatedFat): self
    {
        $this->saturatedFat = (float) $saturatedFat;

        return $this;
    }

    /** @return Collection<int, MealProductWeight>|MealProductWeight[] */
    public function getWeights(): Collection
    {
        return $this->weights;
    }

    /** @param Collection<int, MealProductWeight> $weights */
    public function setWeights(Collection $weights): self
    {
        $this->weights = $weights;
        return $this;
    }

    /**
     * @return MealProduct
     */
    public function addWeight(MealProductWeight $productWeight) {
        $this->weights->add($productWeight);
        return $this;
    }

    public function getCholesterol(): float
    {
        return $this->cholesterol;
    }

    public function setCholesterol(string|float|int $cholesterol): self
    {
        $this->cholesterol = (float) $cholesterol;
        return $this;
    }

    /** @return array<mixed> */
    public function weightList(): array
    {
        return array_map(static function(MealProductWeight $x) {
            return [
                'id' => $x->getId(),
                'name' => $x->getName(),
                'weight' => $x->getWeight(),
                'locale' => $x->getLocale(),
            ];
        }, $this->getWeights()->toArray());
    }

    public function removeWeight(MealProductWeight $weight): void
    {
        $this->weights->removeElement($weight);
    }

    private ?int $excelId = null;

    public function setExcelId(?int $excelId): self
    {
        $this->excelId = $excelId;

        return $this;
    }

    public function getExcelId(): ?int
    {
        return $this->excelId;
    }

    private bool $deleted = false;

    public function setDeleted(bool $deleted): self
    {
        $this->deleted = $deleted;

        return $this;
    }

    public function getDeleted(): bool
    {
        return $this->deleted;
    }

    private bool $allowSplit = true;

    public function setAllowSplit(bool $allowSplit): self
    {
        $this->allowSplit = $allowSplit;

        return $this;
    }

    public function getAllowSplit(): bool
    {
        return $this->allowSplit;
    }

    /** @var Collection<int, MealProductLanguage> */
    private Collection $mealProductLanguages;

    /** @return Collection<int, MealProductLanguage> */
    public function getMealProductLanguages(): Collection
    {
        return $this->mealProductLanguages;
    }

    public function getMealProductLanguageByLocale($locale)
    {
        if ($this->mealProductLanguages->count() === 0) {
            return null;
        }

        $row = $this->mealProductLanguages
            ->filter(function(MealProductLanguage $mpl) use ($locale) {
                $language = $mpl->getLanguage();
                return $language && $language->getLocale() === $locale;
            })->last();

        if (!$row) {
            // if no local language, return english locale
            $row = $this->mealProductLanguages
                ->filter(function(MealProductLanguage $mpl) {
                    return $mpl->getLanguage()->getLocale() === 'en';
                })->last();
        }

        if (!$row) {
            //worst case: return name from MealProduct entity
            $row = $this;
        }

        return $row;
    }

    /**
     * @var string|null
     */
    private $label;


    /**
     * Set label.
     *
     * @param string|null $label
     *
     * @return MealProduct
     */
    public function setLabel($label = null)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Get label.
     *
     * @return string|null
     */
    public function getLabel()
    {
        return $this->label;
    }

    private ?MealProductMeta $mealProductMeta = null;

    /**
     * Get mealProductMeta.
     *
     * @return ?MealProductMeta
     */
    public function getMealProductMeta(): ?MealProductMeta
    {
        return $this->mealProductMeta;
    }

    /**
     * @return array<string, mixed>
     */
    public function serializedMealProductMeta(): array
    {
        $mealProductMeta = $this->getMealProductMeta();
        if(!$mealProductMeta) {
            return [];
        }

        return $mealProductMeta->serialize();
    }

}
