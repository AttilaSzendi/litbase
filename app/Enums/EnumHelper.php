<?php

namespace App\Enums;

trait EnumHelper
{
    public static function all(): array
    {
        $values = [];

        foreach (static::cases() as $case) {
            $values[] = $case->value;
        }

        return $values;
    }

    public static function random(): mixed
    {
        $key = array_rand(self::all());

        return self::all()[$key];
    }
}
