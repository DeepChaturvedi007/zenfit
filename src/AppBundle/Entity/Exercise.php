<?php declare(strict_types=1);

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class Exercise
{
    private ?int $id = null;
    private ?int $muscleGroupId = null;
    private ?int $exerciseTypeId = null;
    private ?int $equipmentId = null;
    private ?int $workoutTypeId = null;
    private string $name;
    private ?string $execution = null;
    private ?string $picture_url = null;
    private ?string $video_url = null;
    private ?int $demo = 0;
    private ?MuscleGroup $muscleGroup = null;
    private ?ExerciseType $exerciseType = null;
    private ?Equipment $equipment = null;
    private ?WorkoutType $workoutType = null;
    private ?User $user = null;
    /** @var Collection<int, WorkoutDay> */
    private Collection $workoutDay;

    public function __construct(string $name)
    {
        $this->name = $name;
        $this->workoutDay = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setMuscleGroupId(?int $muscleGroupId = null): self
    {
        $this->muscleGroupId = $muscleGroupId;

        return $this;
    }

    public function getMuscleGroupId(): ?int
    {
        return $this->muscleGroupId;
    }

    public function setExerciseTypeId(?int $exerciseTypeId): self
    {
        $this->exerciseTypeId = $exerciseTypeId;

        return $this;
    }

    public function getExerciseTypeId(): ?int
    {
        return $this->exerciseTypeId;
    }

    public function setEquipmentId(?int $equipmentId): self
    {
        $this->equipmentId = $equipmentId;

        return $this;
    }

    public function getEquipmentId(): ?int
    {
        return $this->equipmentId;
    }

    public function setWorkoutTypeId(?int $workoutTypeId = null): self
    {
        $this->workoutTypeId = $workoutTypeId;

        return $this;
    }

    public function getWorkoutTypeId(): ?int
    {
        return $this->workoutTypeId;
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

    public function setExecution(?string $value): self
    {
        $this->execution = $value;

        return $this;
    }

    public function getExecution(): ?string
    {
        return $this->execution;
    }

    public function setPictureUrl(?string $pictureUrl = null): self
    {
        $this->picture_url = !empty($pictureUrl) ? $pictureUrl : null;

        return $this;
    }

    public function getPictureUrl(): ?string
    {
        return $this->picture_url;
    }

    public function setVideoUrl(?string $videoUrl): self
    {
        $this->video_url = !empty($videoUrl) ? $videoUrl : null;;

        return $this;
    }

    public function getVideoUrl(): ?string
    {
        return $this->video_url;
    }

    public function setDemo(?int $demo): self
    {
        $this->demo = $demo;

        return $this;
    }

    public function getDemo(): ?int
    {
        return $this->demo;
    }

    public function setMuscleGroup(?MuscleGroup $muscleGroup): self
    {
        $this->muscleGroup = $muscleGroup;

        return $this;
    }

    public function getMuscleGroup(): ?MuscleGroup
    {
        return $this->muscleGroup;
    }

    public function setExerciseType(?ExerciseType $exerciseType = null): self
    {
        $this->exerciseType = $exerciseType;

        return $this;
    }

    public function getExerciseType(): ?ExerciseType
    {
        return $this->exerciseType;
    }

    public function setEquipment(?Equipment $equipment): self
    {
        $this->equipment = $equipment;

        return $this;
    }

    public function getEquipment(): ?Equipment
    {
        return $this->equipment;
    }

    public function setWorkoutType(?WorkoutType $workoutType): self
    {
        $this->workoutType = $workoutType;

        return $this;
    }

    public function getWorkoutType(): ?WorkoutType
    {
        return $this->workoutType;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    /** @return Collection<int, WorkoutDay> */
    public function getWorkoutDay(): Collection
    {
        return $this->workoutDay;
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
}
