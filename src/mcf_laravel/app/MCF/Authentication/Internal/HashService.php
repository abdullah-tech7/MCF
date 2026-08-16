<?php

declare(strict_types=1);

namespace App\MCF\Authentication\Internal;

use Illuminate\Support\Facades\Hash;

final class HashService
{
    public static function make(string $value): string
    {
        return Hash::make($value);
    }

    public static function check(string $value, string $hashedValue): bool
    {
        return Hash::check($value, $hashedValue);
    }
}
