<?php

namespace App\Messages\Expense;

use App\Enums\Expense\DescriptionEnum;
use App\Messages\BaseMessage;

abstract class ExpenseMessage extends BaseMessage
{
    public static function descriptionIsRequired(): string
    {
        return sprintf(self::FIELD_REQUIRED, 'description');
    }

    public static function descriptionMaxLength(): string
    {
        return sprintf(self::FIELD_MAX_LENGTH, 'description', DescriptionEnum::MAX_LENGTH->value);
    }

    public static function descriptionType(): string
    {
        return sprintf(self::FIELD_TYPE, 'description', 'string');
    }

    public static function priceIsRequired(): string
    {
        return sprintf(self::FIELD_REQUIRED, 'price.');
    }

    public static function priceType(): string
    {
        return sprintf(self::FIELD_TYPE, 'price', 'numeric.');
    }

    public static function priceIsNotValid(): string
    {
        return sprintf(self::FIELD_TYPE, 'price', 'positive valid number.');
    }

    public static function dateIsRequired(): string
    {
        return sprintf(self::FIELD_REQUIRED, 'date');
    }

    public static function dateIsInvalid(): string
    {
        return sprintf(self::FIELD_TYPE, 'date', "in the format YYYY-MM-DD and ensure it is not later than today's date.");
    }
}
