<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class ExerciseType
{
    private ?int $id = null;

    /** @var Collection<int, Exercise> */
    private Collection $exercises;

    public function __construct(private string $name)
    {
        $this->exercises = new ArrayCollection();
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

    /**
     * Add exercise
     *
     *
     * @return ExerciseType
     */
    public function addExercise(\AppBundle\Entity\Exercise $exercise)
    {
        $this->exercises[] = $exercise;

        return $this;
    }

    /**
     * Remove exercise
     */
    public function removeExercise(\AppBundle\Entity\Exercise $exercise)
    {
        $this->exercises->removeElement($exercise);
    }

    /**
     * @return Collection<int, Exercise>
     */
    public function getExercises(): Collection
    {
        return $this->exercises;
    }
}
