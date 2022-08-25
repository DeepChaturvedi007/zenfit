<?php declare(strict_types=1);

namespace AppBundle\Entity;

use App\EntityIdTrait;

class WorkoutPlanMeta
{
    const LEVELS = [
        1 => 'Beginner',
        2 => 'Intermediate',
        3 => 'Advanced'
    ];

    const LOCATION = [
        1 => 'Gym',
        2 => 'Home',
        3 => 'Outdoor'
    ];

    use EntityIdTrait;

    private ?int $level = null;
    private ?int $type = null;
    private ?int $location = null;
    private ?int $duration = null;
    private ?int $workoutsPerWeek = null;
    private ?int $gender = null;
    private WorkoutPlan $plan;

    public function __construct(WorkoutPlan $plan)
    {
        $this->plan = $plan;
    }

    public function setLevel(?int $level = null): self
    {
        $this->level = $level;
        return $this;
    }

    public function getLevel(): ?int
    {
        return $this->level;
    }

    public function setType(?int $type = null): self
    {
        $this->type = $type;
        return $this;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setLocation(?int $location = null): self
    {
        $this->location = $location;
        return $this;
    }

    public function getLocation(): ?int
    {
        return $this->location;
    }

    public function setDuration(?int $duration = null): self
    {
        $this->duration = $duration;
        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setWorkoutsPerWeek(?int $workoutsPerWeek = null): self
    {
        $this->workoutsPerWeek = $workoutsPerWeek;
        return $this;
    }

    public function getWorkoutsPerWeek(): ?int
    {
        return $this->workoutsPerWeek;
    }

    public function setGender(?int $gender = null): self
    {
        $this->gender = $gender;
        return $this;
    }

    public function getGender(): ?int
    {
        return $this->gender;
    }

    public function setPlan(WorkoutPlan $plan): self
    {
        $this->plan = $plan;
        return $this;
    }

    public function getPlan(): WorkoutPlan
    {
        return $this->plan;
    }
}
