<?php

namespace AppBundle\Entity;

use App\EntityIdTrait;

/**
 * WorkoutPlanSettings
 */
class WorkoutPlanSettings
{
    use EntityIdTrait;

    static public $allowedField = [
        'rm',
        'tempo',
        'weight',
        'rest',
        'reps',
        'time',
        'sets',
    ];

    /**
     * @var boolean
     */
    private $sets = true;

    /**
     * @var boolean
     */
    private $reps = true;

    /**
     * @var boolean
     */
    private $rest = true;

    /**
     * @var boolean
     */
    private $weight = false;

    /**
     * @var boolean
     */
    private $tempo = false;

    /**
     * @var boolean
     */
    private $rm = false;

    /**
     * @var boolean
     */
    private $time = true;

    private WorkoutPlan $plan;

    public function __construct(WorkoutPlan $plan)
    {
        $this->plan = $plan;
    }

    /**
     * @return bool
     */
    public function getSets()
    {
        return $this->sets;
    }

    /**
     * @param bool $sets
     * @return WorkoutPlanSettings
     */
    public function setSets($sets)
    {
        $this->sets = $sets;
        return $this;
    }

    /**
     * @return bool
     */
    public function getReps()
    {
        return $this->reps;
    }

    /**
     * @param bool $reps
     * @return WorkoutPlanSettings
     */
    public function setReps($reps)
    {
        $this->reps = $reps;
        return $this;
    }

    /**
     * @return bool
     */
    public function getRest()
    {
        return $this->rest;
    }

    /**
     * @param bool $rest
     * @return WorkoutPlanSettings
     */
    public function setRest($rest)
    {
        $this->rest = $rest;
        return $this;
    }

    /**
     * @return bool
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * @param bool $weight
     * @return WorkoutPlanSettings
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;
        return $this;
    }

    /**
     * @return bool
     */
    public function getTempo()
    {
        return $this->tempo;
    }

    /**
     * @param bool $tempo
     * @return WorkoutPlanSettings
     */
    public function setTempo($tempo)
    {
        $this->tempo = $tempo;
        return $this;
    }

    /**
     * @return bool
     */
    public function getRm()
    {
        return $this->rm;
    }

    /**
     * @param bool $rm
     * @return WorkoutPlanSettings
     */
    public function setRm($rm)
    {
        $this->rm = $rm;
        return $this;
    }

    public function getPlan(): WorkoutPlan
    {
        return $this->plan;
    }

    public function setPlan(WorkoutPlan $plan): self
    {
        $this->plan = $plan;
        return $this;
    }

    /**
     * Set time
     *
     * @param boolean $time
     *
     * @return WorkoutPlanSettings
     */
    public function setTime($time)
    {
        $this->time = $time;

        return $this;
    }

    /**
     * Get time
     *
     * @return boolean
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'sets' => $this->getSets(),
            'reps' => $this->getReps(),
            'time' => $this->getTime(),
            'rm' => $this->getRm(),
            'weight' => $this->getWeight(),
            'rest' => $this->getRest(),
            'tempo' => $this->getTempo(),
        ];
    }

}
