<?php declare(strict_types=1);

namespace AppBundle\Entity;

class MasterMealPlanMeta
{
    private ?int $id = null;

    private ?int $type = null;

    private int $duration;

    private MasterMealPlan $plan;

    public function __construct(MasterMealPlan $plan, int $duration)
    {
        $this->plan = $plan;
        $this->duration = $duration;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setType(?int $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setDuration(int $duration): self
    {
        $this->duration = $duration;

        return $this;
    }

    public function getDuration(): int
    {
        return $this->duration;
    }

    public function setPlan(?MasterMealPlan $plan): self
    {
        $this->plan = $plan;

        return $this;
    }

    public function getPlan(): ?MasterMealPlan
    {
        return $this->plan;
    }
}
