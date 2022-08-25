<?php

namespace AppBundle\Event;

use AppBundle\Entity\Exercise;
use AppBundle\Entity\MealProduct;
use Symfony\Contracts\EventDispatcher\Event;
use AppBundle\Entity\User;
use AppBundle\Entity\ActivityLog;


class TrainerMadeChangesEvent extends Event
{
    const USED_EXERCISE = 'trainer.used_exercise';
    const USED_FOOD_ITEM = 'trainer.used_food_item';
    const LOGIN = 'trainer.login';
    const SENT_PUSH_MSG = 'trainer.sent_push_msg';

    public function __construct(protected User $user, protected $name, protected ?Exercise $exercise = null, protected ?MealProduct $mealProduct = null)
    {
    }

    public function getName()
    {
        return $this->name;
    }

    public function getExercise()
    {
        return $this->exercise;
    }

    public function getMealProduct()
    {
        return $this->mealProduct;
    }

    public function getUser()
    {
        return $this->user;
    }


}
