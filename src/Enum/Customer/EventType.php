<?php

namespace App\Enum\Customer;

class EventType
{
    public const EVENT_PROSPECT_CREATED = 'prospect_created';
    public const EVENT_CUSTOMER_CREATED = 'customer_created';
    public const EVENT_EMAIL_SENT = 'email_sent';
    public const EVENT_MEETiNG_CUSTOMER_REQUESTED = 'meeting_customer_requested';
    public const EVENT_MEETiNG_COMMERCIAL_REQUESTED = 'meeting_commercial_requested';
    public const EVENT_MEETING = 'meeting';
    public const EVENT_QUOTATION_REQUESTED = 'quotation_requested';
    public const EVENT_QUOTATION_SENT = 'quotation_sent';
    public const INVOICE_SENT = 'invoice_sent';
    public const INVOICE_PAID = 'invoice_paid';

    public static function getValues(): array
    {
        $values = [
            self::EVENT_PROSPECT_CREATED,
            self::EVENT_CUSTOMER_CREATED,
            self::EVENT_EMAIL_SENT,
            self::EVENT_MEETiNG_CUSTOMER_REQUESTED,
            self::EVENT_MEETiNG_COMMERCIAL_REQUESTED,
            self::EVENT_MEETING,
            self::EVENT_QUOTATION_REQUESTED,
            self::EVENT_QUOTATION_SENT,
            self::INVOICE_SENT,
            self::INVOICE_PAID,
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