<?php

namespace AppBundle\Enums;

use Elao\Enum\ReadableEnum;

/** @extends ReadableEnum<int> */
final class MealType extends ReadableEnum
{
    const BREAKFAST = 1;
    const LUNCH = 2;
    const DINNER = 3;
    const MORNING_SNACK = 4;
    const AFTERNOON_SNACK = 5;
    const EVENING_SNACK = 6;

    public static function values(): array
    {
        return [
            self::BREAKFAST,
            self::LUNCH,
            self::DINNER,
            self::MORNING_SNACK,
            self::AFTERNOON_SNACK,
            self::EVENING_SNACK
        ];
    }

    public static function readables(): array
    {
        return [
            self::BREAKFAST => 'Breakfast',
            self::LUNCH => 'Lunch',
            self::DINNER => 'Dinner',
            self::MORNING_SNACK => 'Morning Snack',
            self::AFTERNOON_SNACK => 'Afternoon Snack',
            self::EVENING_SNACK => 'Evening Snack'
        ];
    }
}
