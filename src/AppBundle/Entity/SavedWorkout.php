<?php

namespace AppBundle\Entity;

/**
 * SavedWorkout
 */
class SavedWorkout
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string|null
     */
    private $time;

    /**
     * @var string|null
     */
    private $comment;

    /**
     * @var \DateTime|null
     */
    private $date;

    /**
     * @var \AppBundle\Entity\Workout
     */
    private $workout;


    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set time.
     *
     * @param string|null $time
     *
     * @return SavedWorkout
     */
    public function setTime($time = null)
    {
        $this->time = $time;

        return $this;
    }

    /**
     * Get time.
     *
     * @return string|null
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * Set comment.
     *
     * @param string|null $comment
     *
     * @return SavedWorkout
     */
    public function setComment($comment = null)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get comment.
     *
     * @return string|null
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Set date.
     *
     * @param \DateTime|null $date
     *
     * @return SavedWorkout
     */
    public function setDate($date = null)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date.
     *
     * @return \DateTime|null
     */
    public function getDate()
    {
        return $this->date;
    }
    /**
     * @var \AppBundle\Entity\WorkoutDay
     */
    private $workoutDay;


    /**
     * Set workoutDay.
     *
     *
     * @return SavedWorkout
     */
    public function setWorkoutDay(\AppBundle\Entity\WorkoutDay $workoutDay)
    {
        $this->workoutDay = $workoutDay;

        return $this;
    }

    /**
     * Get workoutDay.
     *
     * @return \AppBundle\Entity\WorkoutDay
     */
    public function getWorkoutDay()
    {
        return $this->workoutDay;
    }
}
