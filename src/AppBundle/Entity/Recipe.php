<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Recipe
 */
class Recipe
{
    const TYPE_BREAKFAST = 1;
    const TYPE_LUNCH = 2;
    const TYPE_DINNER = 3;
    const TYPE_MORNING_SNACK = 4;
    const TYPE_AFTERNOON_SNACK = 5;
    const TYPE_EVENING_SNACK = 6;

    const TIME_FAST = 1;
    const TIME_MID = 2;
    const TIME_MID_SLOW = 3;
    const TIME_SLOW = 4;

    public const LOCALES = [
        Language::LOCALE_DK,
        Language::LOCALE_NO,
        Language::LOCALE_SV,
        Language::LOCALE_FI,
        Language::LOCALE_NL,
        Language::LOCALE_DE,
        Language::LOCALE_EN
    ];

    private ?int $id = null;
    private string $name;
    private string $locale;
    private int $macroSplit = 0;
    private ?User $user = null;
    private ?string $image = null;
    private ?int $excelId = null;
    /** @var Collection<int, RecipeProduct> */
    private Collection $products;
    /** @var Collection<int, MealPlan> */
    private Collection $mealPlans;
    private ?string $comment = null;
    private \DateTime $createdAt;
    private \DateTime $updatedAt;
    private bool $isSpecial = false;
    private ?RecipeMeta $recipeMeta = null;
    private bool $deleted = false;
    /** @var Collection<int, RecipeType> */
    private Collection $types;
    private int $type = 0;
    private bool $approved = true;
    private int $cookingTime = 0;
    /** @var Collection<int, RecipePreference> */
    private Collection $preferences;
    private ?Recipe $parent = null;
    /** @var Collection<int, Recipe> */
    private Collection $children;

    public function __construct(string $name, string $locale)
    {
        $this->name = $name;
        $this->locale = $locale;
        $now = new \DateTime('now');
        $this->createdAt = $now;
        $this->updatedAt = $now;
        $this->products = new ArrayCollection();
        $this->mealPlans = new ArrayCollection();
        $this->types = new ArrayCollection();
        $this->preferences = new ArrayCollection();
        $this->children = new ArrayCollection();
    }

    /** @return array<mixed> */
    public function getTotals(): array
    {
        $results = [
            'protein' => 0,
            'carbohydrate' => 0,
            'fat' => 0,
            'weight' => 0,
            'kcal' => 0,
        ];

        foreach ($this->getProducts() as $item) {
            /**
             * @var $item RecipeProduct
             * @var $product MealProduct
             */
            $product = $item->getProduct();
            $weight = $item->getTotalWeight();

            $fat = ($weight / 100) * $product->getFat();
            $carbohydrate = ($weight / 100) * $product->getCarbohydrates();
            $protein = ($weight / 100) * $product->getProtein();

            $results['protein'] += max(0, $protein);
            $results['carbohydrate'] += max(0, $carbohydrate);
            $results['fat'] += max(0, $fat);
            $results['weight'] += $weight;
            $results['kcal'] += $item->getTotalKcal();
        }

        $results['protein'] = round($results['protein'], 0);
        $results['carbohydrate'] = round($results['carbohydrate'], 0);
        $results['fat'] = round($results['fat'], 0);
        $results['weight'] = round($results['weight'], 0);
        $results['kcal'] = round($results['kcal'], 0);

        return $results;
    }

    /**
     * @return float
     */
    public function getKcals()
    {
        return $this->getTotals()['kcal'];
    }

    public function getParentAndChildrenRecipes()
    {
        return $this->getChildren()->merge(collect([$this]));
    }

    public function getParentAndChildrenByLocale($locale)
    {
        return collect($this->children)
            ->merge(collect([$this]))
            ->filter(function ($recipe) use ($locale) {
                return $recipe->getLocale() === $locale;
            });
    }

    public function getAllRecipeTitlesAndDescriptions()
    {
        return collect(self::LOCALES)
            ->mapWithKeys(function($locale) {
                $recipe = $this->getParentAndChildrenByLocale($locale)->first();
                if (!$recipe) return [];
                return [$locale => [
                    'title' => $recipe->getName(),
                    'description' => $recipe->getComment()
                ]];
            });
    }

    public function groupRecipesByLocaleAndIngredients()
    {
        $recipes = collect($this->children)
            ->merge(collect([$this]))
            ->filter(function($recipe) {
                return !$recipe->getDeleted();
            })
            ->map(function ($recipe) {
                return [
                    'locale' => $recipe->getLocale(),
                    'approved' => $recipe->getApproved(),
                    'macro_split' => $recipe->getMacroSplit(),
                    'errors' => $recipe->errorsInRecipe(),
                    'lactose_free' => !$recipe->getRecipeMeta()->getLactose(),
                    'gluten_free' => !$recipe->getRecipeMeta()->getGluten(),
                ];
            });

        $collection = collect(self::LOCALES)
            ->mapWithKeys(function($locale) use ($recipes) {
                $r = $recipes
                    ->filter(function ($recipe) use ($locale) {
                        return $recipe['locale'] == $locale;
                    });

                return [$locale => $r];
            })
            ->map(function ($recipes) {
                $approved = $recipes
                    ->filter(function($recipe) {
                        return $recipe['approved'];
                    });
                $glutenFree = $approved
                    ->filter(function($recipe) {
                        return $recipe['gluten_free'];
                    })->count();
                $lactoseFree = $approved
                    ->filter(function($recipe) {
                        return $recipe['lactose_free'];
                    })->count();
                $lactoseAndGlutenFree = $approved
                    ->filter(function($recipe) {
                        return $recipe['lactose_free'] && $recipe['gluten_free'];
                    })->count();
                $errors = $approved
                    ->filter(function($recipe) {
                        return $recipe['errors'];
                    })->count();

                return [
                    'total' => $recipes->count(),
                    'approved' => $approved->count(),
                    'errors' => $errors,
                    'glutenFree' => $glutenFree,
                    'lactoseFree' => $lactoseFree,
                    'glutenAndLactoseFree' => $lactoseAndGlutenFree
                ];
            });

        return $collection;
    }

    public function errorsInRecipe()
    {
        $locale = $this->locale;
        $count = collect($this->products)
            ->filter(function($recipeProduct) use ($locale) {
                //check if local translation exists of ingredient
                $localeExists = $recipeProduct
                    ->getProduct()
                    ->getMealProductLanguages()
                    ->filter(function($mpl) use ($locale) {
                        return $mpl->getLanguage()->getLocale() === $locale;
                    })->last();

                if ($localeExists) {
                    return false;
                }

                return true;
            })->count();

        return $count;
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

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function setLocale(string $locale): self
    {
        $this->locale = $locale;
        return $this;
    }

    public function getMacroSplit(): int
    {
        return $this->macroSplit;
    }

    public function setMacroSplit(int $macroSplit): self
    {
        $this->macroSplit = $macroSplit;
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

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;
        return $this;
    }

    /**
     * @return RecipeProduct[]
     */
    public function getProducts(): array
    {
        return $this->products->toArray();
    }

    /**
     * @param ArrayCollection $products
     * @return Recipe
     */
    public function setProducts($products)
    {
        $this->products = $products;
        return $this;
    }

    /** @return Collection<int, MealPlan> */
    public function getMealPlans(): Collection
    {
        return $this->mealPlans;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;
        return $this;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTime $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }


    // Events

    public function onAdd()
    {
        $now = new \DateTime('now');
        $this->setCreatedAt($now);
        $this->setUpdatedAt($now);
    }

    public function onUpdate()
    {
        $this->setUpdatedAt(new \DateTime('now'));
    }

    public function setExcelId(?int $excelId): self
    {
        $this->excelId = $excelId;

        return $this;
    }

    public function getExcelId(): ?int
    {
        return $this->excelId;
    }

    public function setDeleted(bool $deleted): self
    {
        $this->deleted = $deleted;

        return $this;
    }

    public function getRecipeMeta(): ?RecipeMeta
    {
        return $this->recipeMeta;
    }

    public function addType(RecipeType $type): self
    {
        if ($this->types->contains($type)) {
            return $this;
        }

        $this->types->add($type);

        return $this;
    }

    /**
     * @return array
     */
    public function typeList(): array
    {
        if(!$this->getTypes()) {
          return [];
        }

        $types = [];
        foreach($this->getTypes() as $t) {
          $types[] = $t->getType();
        }

        return $types;
    }

    /** @return array<mixed> */
    public function serializedRecipeMeta(): array
    {
        if(!$this->getRecipeMeta()) {
          return [];
        }

        return $this->getRecipeMeta()->serialize();
    }

    /** @return Collection<int, RecipeType> */
    public function getTypes(): Collection
    {
        return $this->types;
    }

    public function getDeleted(): bool
    {
        return $this->deleted;
    }

    public function setRecipeMeta(?RecipeMeta $recipeMeta): self
    {
        $this->recipeMeta = $recipeMeta;

        return $this;
    }

    public function setType(int $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function setApproved(bool $approved): self
    {
        $this->approved = $approved;

        return $this;
    }

    public function getApproved(): bool
    {
        return $this->approved;
    }

    public function setCookingTime(int $cookingTime): self
    {
        $this->cookingTime = $cookingTime;

        return $this;
    }

    public function isSpecial(): bool
    {
        return $this->isSpecial;
    }

    public function setIsSpecial(bool $isSpecial): self
    {
        $this->isSpecial = $isSpecial;

        return $this;
    }

    public function getCookingTime(): int
    {
        return $this->cookingTime;
    }

    /** @return Collection<int, RecipePreference> */
    public function getPreferences(): Collection
    {
        return $this->preferences;
    }

    public function setParent(Recipe $parent = null): self
    {
        $this->parent = $parent;

        return $this;
    }

    public function getParent(): ?Recipe
    {
        return $this->parent;
    }

    /** @return \Illuminate\Support\Collection<mixed> */
    public function getChildren(): \Illuminate\Support\Collection
    {
        return collect($this->children)
            ->filter(function ($recipe) {
                return !$recipe->getDeleted();
            })->sortBy(function ($recipe) {
                return $recipe->getLocale();
            });
    }
}
