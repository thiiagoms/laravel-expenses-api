<?php

namespace App\Messages\User;

use App\Enums\User\NameEnum;
use App\Enums\Auth\PasswordEnum;
use App\Messages\BaseMessage;

abstract class UserMessage extends BaseMessage
{
    public static function nameIsRequired(): string
    {
        return sprintf(self::FIELD_REQUIRED, 'name');
    }

    public static function nameMinLength(): string
    {
        return sprintf(self::FIELD_MIN_LENGTH, 'name', NameEnum::MIN_LENGTH->value);
    }

    public static function nameMaxLength(): string
    {
        return sprintf(self::FIELD_MAX_LENGTH, 'name', NameEnum::MAX_LENGTH->value);
    }

    public static function nameType(): string
    {
        return sprintf(self::FIELD_TYPE, 'name', 'string');
    }

    public static function emailIsRequired(): string
    {
        return sprintf(self::FIELD_REQUIRED, 'email');
    }

    public static function emailInvalid(): string
    {
        return sprintf(self::FIELD_TYPE, 'email', 'e-mail');
    }

    public static function emailAlreadyExists(): string
    {
        return sprintf(self::RECORD_ALREADY_EXISTS, 'email');
    }

    public static function passwordIsRequired(): string
    {
        return sprintf(self::FIELD_REQUIRED, 'password');
    }

    public static function passwordMinLength(): string
    {
        return sprintf(self::FIELD_MIN_LENGTH, 'password', PasswordEnum::MIN_LENGTH->value);
    }

    public static function passwordNumbers(): string
    {
        return 'The password field must contain at least one number.';
    }

    public static function passwordSymbols(): string
    {
        return 'The password field must contain at least one symbol.';
    }

    public static function passwordMixedCase(): string
    {
        return 'The password field must contain at least one uppercase and one lowercase letter.';
    }

    public static function invalidStatus(): string
    {
        return 'Your account must be active or not blocked to log in. Please contact support if you need assistance.';
    }
}
