<?php

namespace AppBundle\Enums;

use Elao\Enum\ReadableEnum;

/** @extends ReadableEnum<int> */
final class CookingTime extends ReadableEnum
{
    const FAST = 1;
    const MID = 2;
    const MID_SLOW = 3;
    const SLOW = 4;

    public static function values(): array
    {
        return [
            self::FAST,
            self::MID,
            self::MID_SLOW,
            self::SLOW
        ];
    }

    public static function readables(): array
    {
        return [
            self::FAST => '0-10 min',
            self::MID => '10-20 min',
            self::MID_SLOW => '20-30 min',
            self::SLOW => '+30 min'
        ];
    }
}
