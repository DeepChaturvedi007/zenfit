<?php

namespace AppBundle\Enums;

use Elao\Enum\ReadableEnum;

/** @extends ReadableEnum<int> */
final class MacroSplit extends ReadableEnum
{
    const C50_P30_F20 = 1;
    const C40_P40_F20 = 2;
    const C10_P30_F20 = 4;
    const C35_P35_F30 = 6;
    #const C50_P20_F30 = 7;

    public static function values(): array
    {
        return [
            self::C50_P30_F20,
            self::C40_P40_F20,
            self::C35_P35_F30,
            self::C10_P30_F20,
            #self::C50_P20_F30
        ];
    }

    public static function readables(): array
    {
        return [
            self::C50_P30_F20 => 'C50 / P30 / F20',
            self::C40_P40_F20 => 'C40 / P40 / F20',
            self::C35_P35_F30 => 'C35 / P35 / F30',
            self::C10_P30_F20 => 'C10 / P30 / F60',
            #self::C50_P20_F30 => 'C50 / P20 / F30'
        ];
    }
}
