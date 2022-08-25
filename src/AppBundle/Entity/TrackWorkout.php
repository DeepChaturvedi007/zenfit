<?php declare(strict_types=1);

namespace AppBundle\Entity;

use App\EntityIdTrait;

class TrackWorkout
{
    use EntityIdTrait;

    private ?string $reps = null;
    private ?string $sets = null;
    private \DateTime $date;
    private Workout $workout;
    private ?string $weight = null;
    private ?string $time = null;
    private bool $deleted = false;

    public function __construct(Workout $workout, \DateTime $date)
    {
        $this->workout = $workout;
        $this->date = $date;
    }

    public function setReps(?string $reps): self
    {
        $this->reps = $reps;

        return $this;
    }

    public function getReps(): ?string
    {
        return $this->reps;
    }

    public function setSets(?string $sets): self
    {
        $this->sets = $sets;

        return $this;
    }

    public function getSets(): ?string
    {
        return $this->sets;
    }

    public function setDate(\DateTime $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getDate(): \DateTime
    {
        return $this->date;
    }

    public function setWorkout(Workout $workout): self
    {
        $this->workout = $workout;

        return $this;
    }

    public function getWorkout(): Workout
    {
        return $this->workout;
    }

    public function setWeight(?string $weight): self
    {
        $this->weight = $weight;

        return $this;
    }

    public function getWeight(): ?string
    {
        return $this->weight;
    }

    public function setTime(?string $time): self
    {
        $this->time = $time;

        return $this;
    }

    public function getTime(): ?string
    {
        return $this->time;
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
}
