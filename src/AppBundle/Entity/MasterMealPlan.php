<?php declare(strict_types=1);

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * MasterMealPlan
 */
class MasterMealPlan
{
    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';
    public const STATUS_HIDDEN = 'hidden';

    public const TYPE_FIXED_SPLIT = 1;
    public const TYPE_CUSTOM_MACROS = 2;

    public const VERSION_V1 = 1;
    public const VERSION_V2 = 2;

    public function getVersion(): int
    {
        if ($this->getContainsAlternatives()) {
            return self::VERSION_V2;
        }
        return self::VERSION_V1;
    }

    public function getMealsSize(): int
    {
        $plans = $this->getMealPlans();
        $size = 0;
        foreach($plans as $plan) {
            $meals = $plan->getChildren();
            $size += count($meals);
        }

        return $size;
    }

    /** @return array<mixed> */
    public function getMealPlansWhereParentIsNull(): array
    {
        $mealPlans = $this->getMealPlans();
        $plans = [];
        foreach($mealPlans as $plan) {
            if (!$plan->getParent() && !$plan->getDeleted()) {
              $plans[] = $plan;
            }
        }

        return $plans;
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

        foreach($this->getMealPlansWhereParentIsNull() as $meal) {
            $totals = $meal->getMealsTotal();
            $results['protein'] += $totals['protein'];
            $results['carbohydrate'] += $totals['carbohydrate'];
            $results['fat'] += $totals['fat'];
            $results['weight'] += $totals['weight'];
            $results['kcal'] += $totals['kcal'];
        }

        return $results;
    }

    /** @return array<mixed> */
    public function getAvgTotals(): array
    {
        $results = [
            'protein' => 0,
            'carbohydrate' => 0,
            'fat' => 0,
            'weight' => 0,
            'kcal' => 0,
        ];

        foreach($this->getMealPlansWhereParentIsNull() as $parent) {
            $avgTotals = array_map(function($val) use ($parent) {
                $count = count($this->getRecipes($parent->getType()));
                return $count == 0 ? 0 : $val / $count;
            }, $parent->getMealsTotal());

            $results['kcal'] += round($avgTotals['kcal']);
            $results['carbohydrate'] += round($avgTotals['carbohydrate']);
            $results['protein'] += round($avgTotals['protein']);
            $results['fat'] += round($avgTotals['fat']);
        }

        return $results;
    }

    /**
     * @return float
     */
    public function getKcals()
    {
        $plans = $this->getMealPlansWhereParentIsNull();
        return $plans ? $plans[0]->getMealsTotal()['kcal'] : 0;
    }

    /**
     * @return array
     */
    public function getMacros()
    {
        $plans = $this->getMealPlansWhereParentIsNull();
        return $plans ? $plans[0]->getMealsTotal() : null;
    }

    /**
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getRecipes($type = null)
    {
        if($type) {
          return $this->getMealPlans()
            ->filter(function($plan) use ($type) {
                return $plan->getParent() && $type == $plan->getType() && !$plan->getDeleted();
            });
        }

        return $this->getMealPlans()
          ->filter(function($plan) {
              return $plan->getParent() && !$plan->getDeleted();
          });
    }

    public function getParameterByKey($key)
    {
        $parameters = json_decode($this->getParameters(), true);
        if (isset($parameters[$key])) {
            return $parameters[$key];
        }

        if ($key === 'foodPreferences') {
            return $parameters['ingredientsToAvoid'] ?? $parameters;
        }

        return [];
    }

    private ?int $id = null;
    private string $name = '';
    private ?string $explaination = null;
    private bool $active = false;
    private string $status = 'active';
    private \DateTime $lastUpdated;
    private \DateTime $createdAt;
    /** @var Collection<int, MealPlan> */
    private Collection $mealPlans;
    private ?Client $client = null;
    private bool $template = false;
    private User $user;
    private bool $demo = false;

    public function __construct(User $user)
    {
        $this->user = $user;
        $this->createdAt = new \DateTime();
        $this->lastUpdated = new \DateTime();
        $this->mealPlans = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return MasterMealPlan
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    public function setExplaination(?string $explaination): self
    {
        $this->explaination = $explaination;

        return $this;
    }

    public function getExplaination(): ?string
    {
        return $this->explaination;
    }

    /**
     * Set active
     *
     * @param boolean $active
     *
     * @return MasterMealPlan
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get active
     *
     * @return boolean
     */
    public function getActive()
    {
        return $this->active;
    }

    public function setLastUpdated(\DateTime $lastUpdated): self
    {
        $this->lastUpdated = $lastUpdated;

        return $this;
    }

    public function getLastUpdated(): \DateTime
    {
        return $this->lastUpdated;
    }

    /** @return Collection<int, MealPlan> */
    public function getMealPlans(): Collection
    {
        return $this->mealPlans;
    }

    /**
     * @return ArrayCollection|\Doctrine\Common\Collections\Collection
     */
    public function getRootMealPlans()
    {
        return $this->mealPlans->filter(function (MealPlan $mealPlan) {
            return !$mealPlan->getParent();
        });
    }

    /**
     * Set client
     *
     * @param \AppBundle\Entity\Client $client
     *
     * @return MasterMealPlan
     */
    public function setClient(\AppBundle\Entity\Client $client = null)
    {
        $this->client = $client;

        return $this;
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    private string $locale = 'en';

    public function setLocale(string $locale): self
    {
        $this->locale = $locale;

        return $this;
    }

    public function getLocale(): string
    {
        return $this->locale;
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

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return bool
     */
    public function isTemplate(): bool
    {
        return $this->template;
    }

    /**
     * @param bool $template
     * @return MasterMealPlan
     */
    public function setTemplate($template)
    {
        $this->template = $template;
        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function isDemo(): bool
    {
        return $this->demo;
    }

    public function setDemo(bool $demo): self
    {
        $this->demo = $demo;
        return $this;
    }

    public function getTemplate(): bool
    {
        return $this->template;
    }

    public function getDemo(): bool
    {
        return $this->demo;
    }

    private ?int $desiredKcals = null;


    public function setDesiredKcals(?int $desiredKcals = null): self
    {
        $this->desiredKcals = $desiredKcals;
        return $this;
    }

    public function getDesiredKcals(): ?int
    {
        return $this->desiredKcals;
    }

    private ?int $macroSplit = null;

    public function setMacroSplit(?int $macroSplit): self
    {
        $this->macroSplit = $macroSplit;

        return $this;
    }

    /**
     * Get macroSplit
     *
     * @return integer
     */
    public function getMacroSplit(): ?int
    {
        return $this->macroSplit;
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

    private bool $containsAlternatives = false;

    public function setContainsAlternatives(bool $containsAlternatives = false): self
    {
        $this->containsAlternatives = $containsAlternatives;
        return $this;
    }

    public function getContainsAlternatives(): bool
    {
        return $this->containsAlternatives;
    }

    private bool $approved = false;

    public function setApproved(bool $approved): self
    {
        $this->approved = $approved;

        return $this;
    }

    public function getApproved(): bool
    {
        return $this->approved;
    }

    private ?string $parameters = null;

    public function setParameters(?string $parameters): self
    {
        $this->parameters = $parameters;

        return $this;
    }

    public function getParameters(): ?string
    {
        return $this->parameters;
    }

    /**
     * @var string|null
     */
    private $image;

    /**
     * @var \DateTime|null
     */
    private $started;

    private ?MasterMealPlanMeta $masterMealPlanMeta = null;

    /**
     * Set image.
     *
     * @param string|null $image
     *
     * @return MasterMealPlan
     */
    public function setImage($image = null)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * Get image.
     *
     * @return string|null
     */
    public function getImage()
    {
        return $this->image;
    }

    public function setStarted(?\DateTime $started): self
    {
        $this->started = $started;

        return $this;
    }

    public function getStarted(): ?\DateTime
    {
        return $this->started;
    }

    public function setMasterMealPlanMeta(?MasterMealPlanMeta $masterMealPlanMeta): self
    {
        $this->masterMealPlanMeta = $masterMealPlanMeta;

        return $this;
    }

    public function getMasterMealPlanMeta(): ?MasterMealPlanMeta
    {
        return $this->masterMealPlanMeta;
    }

    /**
     * @var int|null
     */
    private $type;


    /**
     * Set type.
     *
     * @param int|null $type
     *
     * @return MasterMealPlan
     */
    public function setType($type = null)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type.
     *
     * @return int|null
     */
    public function getType()
    {
        return $this->type;
    }
    /**
     * @var string|null
     */
    private $assignmentTags;


    /**
     * Set assignmentTags.
     *
     * @param string|null $assignmentTags
     *
     * @return MasterMealPlan
     */
    public function setAssignmentTags($assignmentTags = null)
    {
        $this->assignmentTags = $assignmentTags;

        return $this;
    }

    /**
     * Get assignmentTags.
     *
     * @return string|null
     */
    public function getAssignmentTags()
    {
        return $this->assignmentTags;
    }
}
