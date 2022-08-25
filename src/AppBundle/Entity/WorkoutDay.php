<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * WorkoutDay
 */
class WorkoutDay
{

    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    private ?string $workoutDayComment = null;

    /**
     * @var integer
     */
    private ?int $order = null;

    /** @var Collection<int, Workout> */
    private Collection $workouts;

    /**
     * @var \AppBundle\Entity\WorkoutPlan
     */
    private $workoutPlan;

    public function __construct()
    {
        $this->workouts = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return WorkoutDay
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

    public function setWorkoutDayComment(?string $workoutDayComment): self
    {
        $this->workoutDayComment = $workoutDayComment;

        return $this;
    }

    public function getWorkoutDayComment(): ?string
    {
        return $this->workoutDayComment;
    }

    public function setOrder(?int $order): self
    {
        $this->order = $order;

        return $this;
    }

    public function getOrder(): ?int
    {
        return $this->order;
    }

    /** @return Collection<int, Workout> */
    public function getWorkouts(): Collection
    {
        return $this->workouts;
    }

    /**
     * Set workoutPlan
     *
     *
     * @return WorkoutDay
     */
    public function setWorkoutPlan(\AppBundle\Entity\WorkoutPlan $workoutPlan)
    {
        $this->workoutPlan = $workoutPlan;

        return $this;
    }

    /**
     * Get workoutPlan
     *
     * @return \AppBundle\Entity\WorkoutPlan
     */
    public function getWorkoutPlan()
    {
        return $this->workoutPlan;
    }
    /**
     * @var string|null
     */
    private $image;


    /**
     * Set image.
     *
     * @param string|null $image
     *
     * @return WorkoutDay
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
}
