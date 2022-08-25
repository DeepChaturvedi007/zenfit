<?php

namespace AppBundle\Enums;

use Elao\Enum\ReadableEnum;

/** @extends ReadableEnum<string> */
final class ChatMessageStatus extends ReadableEnum
{
    const PENDING = 'pending';
    const DELIVERED = 'delivered';
    const FAILED = 'failed';
    const READ = 'read';

    public static function values(): array
    {
        return [
            self::PENDING,
            self::DELIVERED,
            self::FAILED,
            self::READ,
        ];
    }

    public static function readables(): array
    {
        return [
            self::PENDING => 'Pending',
            self::DELIVERED => 'Delivered',
            self::FAILED => 'Failed',
            self::READ => 'Read',
        ];
    }
}
