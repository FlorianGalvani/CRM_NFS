<?php

namespace App\Enum\Account;

final class AccountType
{
    public const ADMIN = 'admin';
    public const COMMERCIAL = 'commercial';
    public const CUSTOMER = 'customer';

    public static function getValues(): array
    {
        $values = [
            self::ADMIN,
            self::COMMERCIAL,
            self::CUSTOMER,
        ];

        return \array_combine($values, $values);
    }

    public static function getValue(string $key): ?string
    {
        return self::getValues()[$key] ?? null;
    }

    public static function getKeys(): array
    {
        return array_keys(self::getValues());
    }
}