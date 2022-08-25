<?php declare(strict_types=1);

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class MealPlan
{
    public const TYPE_BREAKFAST = 1;
    public const TYPE_LUNCH = 2;
    public const TYPE_DINNER = 3;
    public const TYPE_MORNING_SNACK = 4;
    public const TYPE_AFTERNOON_SNACK = 5;
    public const TYPE_EVENING_SNACK = 6;
    public const TYPE_OTHER = 0;

    public const MEAL_PERCENTAGE_SPLIT = [
      3 => [0.33, 0.33, 0.34],
      4 => [0.28, 0.28, 0.28, 0.16],
      5 => [0.23, 0.15, 0.23, 0.15, 0.24],
      6 => [0.22, 0.11, 0.22, 0.11, 0.23, 0.11],
      7 => [0.16, 0.16, 0.16, 0.16, 0.12, 0.12, 0.12]
    ];

    public const MEAL_TYPE_PERCENTAGE =
    [
      3 => [
        self::TYPE_BREAKFAST => 0.33,
        self::TYPE_LUNCH => 0.33,
        self::TYPE_DINNER => 0.34,
        self::TYPE_MORNING_SNACK => 0.15,
        self::TYPE_AFTERNOON_SNACK => 0.15,
        self::TYPE_EVENING_SNACK => 0.15,
        self::TYPE_OTHER => 0.1
      ],
      4 => [
        self::TYPE_BREAKFAST => 0.28,
        self::TYPE_LUNCH => 0.28,
        self::TYPE_DINNER => 0.28,
        self::TYPE_MORNING_SNACK => 0.15,
        self::TYPE_AFTERNOON_SNACK => 0.15,
        self::TYPE_EVENING_SNACK => 0.15,
        self::TYPE_OTHER => 0.1
      ],
      5 => [
        self::TYPE_BREAKFAST => 0.23,
        self::TYPE_LUNCH => 0.23,
        self::TYPE_DINNER => 0.24,
        self::TYPE_MORNING_SNACK => 0.15,
        self::TYPE_AFTERNOON_SNACK => 0.15,
        self::TYPE_EVENING_SNACK => 0.15,
        self::TYPE_OTHER => 0.1
      ],
      6 => [
        self::TYPE_BREAKFAST => 0.22,
        self::TYPE_LUNCH => 0.22,
        self::TYPE_DINNER => 0.23,
        self::TYPE_MORNING_SNACK => 0.11,
        self::TYPE_AFTERNOON_SNACK => 0.11,
        self::TYPE_EVENING_SNACK => 0.11,
        self::TYPE_OTHER => 0.1
      ],
      7 => [
        self::TYPE_BREAKFAST => 0.22,
        self::TYPE_LUNCH => 0.22,
        self::TYPE_DINNER => 0.23,
        self::TYPE_MORNING_SNACK => 0.11,
        self::TYPE_AFTERNOON_SNACK => 0.11,
        self::TYPE_EVENING_SNACK => 0.11,
        self::TYPE_OTHER => 0.1
      ]
    ];

    public const MACRO_SPLIT = [
      1 => [
        'title' => '50/30/20',
        'carbohydrate' => 0.5,
        'protein' => 0.3,
        'fat' => 0.2
      ],
      2 => [
        'title' => '40/40/20',
        'carbohydrate' => 0.4,
        'protein' => 0.4,
        'fat' => 0.2
      ],
      4 => [
        'title' => '10/30/60',
        'carbohydrate' => 0.1,
        'protein' => 0.3,
        'fat' => 0.6
      ],
      6 => [
        'title' => '35/35/30',
        'carbohydrate' => 0.35,
        'protein' => 0.35,
        'fat' => 0.30
      ],
      /*7 => [
        'title' => '50/20/30',
        'carbohydrate' => 0.50,
        'protein' => 0.20,
        'fat' => 0.30
      ]*/
    ];

    /**
     * @return integer
     */
    public function getMealsSize()
    {
        $meals = $this->getChildren();
        return sizeof($meals);
    }

    private ?int $id = null;
    private MasterMealPlan $masterMealPlan;
    /** @var Collection<int, MealPlan> */
    private Collection $children;
    private ?MealPlan $parent = null;
    private ?Client $client = null;
    /** @var Collection<int, MealPlanProduct> */
    private Collection $products;
    private ?string $name = null;
    private ?string $comment = null;
    private int $order = 1;
    private \DateTime $createdAt;

    public function __construct(MasterMealPlan $masterMealPlan)
    {
        $this->masterMealPlan = $masterMealPlan;
        $this->children = new ArrayCollection();
        $this->products = new ArrayCollection();
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getParent(): ?MealPlan
    {
        return $this->parent;
    }

    public function setParent(?MealPlan $parent = null): self
    {
        $this->parent = $parent;
        return $this;
    }

    /** @return \Illuminate\Support\Collection<MealPlan> */
    public function getChildren(): \Illuminate\Support\Collection
    {
        $masterMeal = $this->getMasterMealPlan();
        return collect($this->children)->filter(function($children) use ($masterMeal) {
            return $children->getMasterMealPlan()->getId() === $masterMeal->getId();
        });
    }

    /**
     * @return Client
     */
    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(?Client $client = null): self
    {
        $this->client = $client;
        return $this;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getOrder(): int
    {
        return $this->order;
    }

    public function setOrder(int $order): self
    {
        $this->order = $order;
        return $this;
    }

    /** @return Collection<int, MealPlanProduct> */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    /** @param Collection<int, MealPlanProduct> $products */
    public function setProducts(Collection $products): self
    {
        $this->products = $products;
        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $value): self
    {
        $this->comment = $value;
        return $this;
    }


    /**
     * This function is for parent meals
     * @return array<mixed>
     */
    public function getMealsTotal(): array
    {
        $results = [
            'protein' => 0,
            'carbohydrate' => 0,
            'fat' => 0,
            'weight' => 0,
            'kcal' => 0,
        ];

        foreach ($this->getChildren() as $meal) {
            $totals = $meal->getTotals();

            $results['protein'] += $totals['protein'];
            $results['carbohydrate'] += $totals['carbohydrate'];
            $results['fat'] += $totals['fat'];
            $results['weight'] += $totals['weight'];
            $results['kcal'] += $totals['kcal'];
        }

        return $results;
    }

    /**
     * This function is for child meals
     * @return array<mixed>
     */
    public function getTotals(): array
    {
        $results = [
            'protein' => 0,
            'carbohydrate' => 0,
            'fat' => 0,
            'weight' => 0,
            'kcal' => 0,
        ];

        foreach ($this->getProducts()->toArray() as $item) {
            /**
             * @var $item MealPlanProduct
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

        $results['protein'] = round($results['protein'],0);
        $results['carbohydrate'] = round($results['carbohydrate'],0);
        $results['fat'] = round($results['fat'],0);
        $results['weight'] = round($results['weight'],0);
        $results['kcal'] = round($results['kcal'],0);

        return $results;
    }

    public function hasProducts(): bool
    {
        if ($this->parent) {
            return !$this->products->isEmpty();
        }

        return true;
    }

    private ?\DateTime $lastUpdated = null;

    public function setLastUpdated(?\DateTime $lastUpdated): self
    {
        $this->lastUpdated = $lastUpdated;

        return $this;
    }

    public function getLastUpdated(): ?\DateTime
    {
        return $this->lastUpdated;
    }

    private bool $active = false;

    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    public function getActive(): bool
    {
        return $this->active;
    }

    public function setMasterMealPlan(MasterMealPlan $masterMealPlan): self
    {
        $this->masterMealPlan = $masterMealPlan;

        return $this;
    }

    public function getMasterMealPlan(): MasterMealPlan
    {
        return $this->masterMealPlan;
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

    private ?string $image = null;


    public function setImage(?string $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
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

    private ?Recipe $recipe = null;

    public function setRecipe(?Recipe $recipe): self
    {
        $this->recipe = $recipe;

        return $this;
    }

    public function getRecipe(): ?Recipe
    {
        return $this->recipe;
    }

    private ?int $macroSplit = null;

    private int $type = 0;

    public function setMacroSplit(?int $macroSplit): self
    {
        $this->macroSplit = $macroSplit;

        return $this;
    }

    public function getMacroSplit(): ?int
    {
        return $this->macroSplit;
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

    private bool $contains_alternatives = false;

    public function setContainsAlternatives(bool $containsAlternatives): self
    {
        $this->contains_alternatives = $containsAlternatives;

        return $this;
    }

    public function getContainsAlternatives(): bool
    {
        return $this->contains_alternatives;
    }

    private ?float $percentWeight = null;


    public function setPercentWeight(string|float|int|null $percentWeight): self
    {
        if (is_string($percentWeight) || is_int($percentWeight)) {
            $percentWeight = (float) $percentWeight;
        }

        $this->percentWeight = $percentWeight;

        return $this;
    }

    public function getPercentWeight(): ?float
    {
        return $this->percentWeight;
    }
}
