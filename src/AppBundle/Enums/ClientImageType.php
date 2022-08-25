<?php

namespace AppBundle\Enums;

use Elao\Enum\ReadableEnum;

/** @extends ReadableEnum<int> */
final class ClientImageType extends ReadableEnum
{
    const FRONT = 1;
    const SIDE = 2;
    const REAR = 3;

    public static function values(): array
    {
        return [
            self::FRONT,
            self::SIDE,
            self::REAR,
        ];
    }

    public static function readables(): array
    {
        return [
            self::FRONT => 'Front',
            self::SIDE => 'Side',
            self::REAR => 'Rear',
        ];
    }
}
