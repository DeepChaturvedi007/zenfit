<?php declare(strict_types=1);

namespace AppBundle\Entity;

use App\EntityIdTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class WorkoutPlan
{
    use EntityIdTrait;

    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_HIDDEN = 'hidden';

    private string $name;
    private string $status = self::STATUS_ACTIVE;
    private ?string $comment = null;
    private ?string $assignmentTags = null;
    private ?string $explaination = null;
    private ?\DateTime $createdAt = null;
    private ?\DateTime $lastUpdated = null;
    private bool $template = false;
    private bool $deleted = false;
    private bool $demo = false;
    private ?WorkoutPlanMeta $workoutPlanMeta = null;
    private ?Client $client = null;
    private ?WorkoutPlanSettings $settings = null;
    private ?User $user = null;
    /** @var Collection<int, WorkoutDay>|WorkoutDay[] */
    private Collection $workoutDays;

    public function __construct(string $name)
    {
        $this->name = $name;
        $this->workoutDays = new ArrayCollection();
    }

    public function getWorkoutDaysSize(): int
    {
        return sizeof($this->getWorkoutDays());
    }

    public function getExercisesSize(): int
    {
        $days = $this->getWorkoutDays();
        $size = 0;
        foreach($days as $day) {
            /** @var $day WorkoutDay */
            $exercises = $day->getWorkouts();
            $size = $size + sizeof($exercises);
        }

        return $size;
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

    public function setClient(?Client $client = null): self
    {
        $this->client = $client;
        return $this;
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setLastUpdated(?\DateTime $lastUpdated): self
    {
        $this->lastUpdated = $lastUpdated;
        return $this;
    }

    public function getLastUpdated(): ?\DateTime
    {
        return $this->lastUpdated;
    }

    public function setExplaination(?string $explaination = null): self
    {
        $this->explaination = $explaination;
        return $this;
    }

    public function getExplaination(): ?string
    {
        return $this->explaination;
    }

    public function getSettings(): ?WorkoutPlanSettings
    {
        return $this->settings;
    }

    public function setSettings(?WorkoutPlanSettings $settings = null): self
    {
        $this->settings = $settings;
        return $this;
    }

    public function setCreatedAt(?\DateTime $createdAt): self
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

    public function isTemplate(): bool
    {
        return $this->template;
    }

    public function setTemplate(bool $template): self
    {
        $this->template = $template;
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

    public function isDemo(): bool
    {
        return $this->demo;
    }

    public function setDemo(bool $demo): self
    {
        $this->demo = $demo;
        return $this;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
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

    public function setWorkoutPlanMeta(WorkoutPlanMeta $workoutPlanMeta = null): self
    {
        $this->workoutPlanMeta = $workoutPlanMeta;
        return $this;
    }

    public function getWorkoutPlanMeta(): ?WorkoutPlanMeta
    {
        return $this->workoutPlanMeta;
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

    public function setAssignmentTags(string $assignmentTags = null): self
    {
        $this->assignmentTags = $assignmentTags;
        return $this;
    }

    public function getAssignmentTags(): ?string
    {
        return $this->assignmentTags;
    }

    /** @return Collection<int, WorkoutDay>|WorkoutDay[] */
    public function getWorkoutDays(): Collection
    {
        return $this->workoutDays;
    }
}
