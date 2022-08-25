<?php

namespace AppBundle\Enums;

use Elao\Enum\ReadableEnum;

/** @extends ReadableEnum<string> */
final class Language extends ReadableEnum
{
    const EN = 'en';
    const DK = 'da_DK';
    const SE = 'sv_SE';
    const NO = 'nb_NO';
    const FI = 'fi_FI';
    const NL = 'nl_NL';
    const DE = 'de_DE';

    public static function values(): array
    {
        return [
            self::EN,
            self::DK,
            self::SE,
            self::NO,
            self::FI,
            self::NL,
            self::DE
        ];
    }

    public static function readables(): array
    {
        return [
            self::EN => 'English',
            self::DK => 'Danish',
            self::SE => 'Swedish',
            self::NO => 'Norwegian',
            self::FI => 'Finnish',
            self::NL => 'Dutch',
            self::DE => 'German'
        ];
    }
}
