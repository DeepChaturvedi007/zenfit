<?php

namespace AppBundle\Entity;

use App\EntityIdTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class Workout
{
    use EntityIdTrait;

    private ?string $info = null;
    private ?string $comment = null;

    /**
     * @var integer
     */
    private $orderBy;

    /** @var Collection<Workout> */
    private Collection $supers;

    /**
     * @var \AppBundle\Entity\Exercise
     */
    private $exercise;

    private WorkoutDay $workoutDay;
    private ?Workout $parent = null;

    /** @var Collection<TrackWorkout> */
    private Collection $tracking;

    public function __construct(WorkoutDay $workoutDay)
    {
        $this->workoutDay = $workoutDay;
        $this->supers = new ArrayCollection();
        $this->tracking = new ArrayCollection();
    }

    public function setInfo(?string $info): self
    {
        $this->info = $info;

        return $this;
    }

    public function getInfo(): ?string
    {
        return $this->info;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;
        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    /**
     * Set orderBy
     *
     * @param integer $orderBy
     * @return Workout
     */
    public function setOrderBy($orderBy)
    {
        $this->orderBy = $orderBy;
        return $this;
    }

    /**
     * Get orderBy
     *
     * @return integer
     */
    public function getOrderBy()
    {
        return $this->orderBy;
    }


    /** @return Collection<int, Workout> */
    public function getSupers(): Collection
    {
        return $this->supers;
    }

    public function setExercise(Exercise $exercise): self
    {
        $this->exercise = $exercise;

        return $this;
    }

    public function getExercise(): Exercise
    {
        return $this->exercise;
    }

    public function setWorkoutDay(WorkoutDay $workoutDay): self
    {
        $this->workoutDay = $workoutDay;

        return $this;
    }

    public function getWorkoutDay(): WorkoutDay
    {
        return $this->workoutDay;
    }

    public function setParent(?Workout $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    public function getParent(): ?Workout
    {
        return $this->parent;
    }

    private ?string $time = null;
    private ?string $reps = null;
    private ?string $rest = null;
    private ?string $startWeight = null;
    private ?string $tempo = null;
    private ?string $rm = null;

    public function setTime(?string $time): self
    {
        $this->time = $time;

        return $this;
    }

    public function getTime(): ?string
    {
        return $this->time;
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

    public function setRest(?string $rest): self
    {
        $this->rest = $rest;

        return $this;
    }

    public function getRest(): ?string
    {
        return $this->rest;
    }

    public function setStartWeight(?string $startWeight): self
    {
        $this->startWeight = $startWeight;

        return $this;
    }

    public function getStartWeight(): ?string
    {
        return $this->startWeight;
    }

    private ?string $sets = null;

    public function setSets(?string $sets): self
    {
        $this->sets = $sets;

        return $this;
    }

    public function getSets(): ?string
    {
        return $this->sets;
    }

    public function getTempo(): ?string
    {
        return $this->tempo;
    }

    public function setTempo(?string $tempo): self
    {
        $this->tempo = $tempo;
        return $this;
    }

    public function getRm(): ?string
    {
        return $this->rm;
    }

    public function setRm(?string $rm): self
    {
        $this->rm = $rm;
        return $this;
    }

    /** @return Collection<int, TrackWorkout> */
    public function getTracking(): Collection
    {
        return $this->tracking;
    }
}
