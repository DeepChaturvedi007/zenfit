<?php

namespace AppBundle\Enums;

use Elao\Enum\ReadableEnum;

/** @extends ReadableEnum<int> */
final class PlanType extends ReadableEnum
{
    const WORKOUT_PLAN = 1;
    const MEAL_PLAN = 2;
    const BOTH = 3;

    public static function values(): array
    {
        return [
            self::WORKOUT_PLAN,
            self::MEAL_PLAN,
            self::BOTH
        ];
    }

    public static function readables(): array
    {
        return [
            self::WORKOUT_PLAN => 'Workout Plan',
            self::MEAL_PLAN => 'Meal Plan',
            self::BOTH => 'Workout + Meal Plan'
        ];
    }
}
