<?php

namespace App\Messages\Auth;

use App\Messages\BaseMessage;

abstract class AuthMessage extends BaseMessage
{
    public static function invalidCredentials(): string
    {
        return 'Invalid credentials';
    }

    public static function unauthorized(): string
    {
        return 'Unauthorized';
    }
}
