<?php

namespace App\Enum\Document;

final class DocumentType
{
    public const QUOTE = 'devis';
    public const INVOICE = 'invoice';

    public static function getValues(): array
    {
        $values = [
            self::QUOTE,
            self::INVOICE,
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