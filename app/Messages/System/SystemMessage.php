<?php

namespace App\Messages\System;

use App\Messages\BaseMessage;

abstract class SystemMessage extends BaseMessage
{
    /**
     * |---------------------------------------------------
     * | Resource messages
     * |---------------------------------------------------
     */
    public const string RESOURCE_NOT_FOUND = 'Resource not found';

    /**
     * |---------------------------------------------------
     * | Parameter messages
     * |---------------------------------------------------
     */
    public const INVALID_PARAMETER = 'Invalid parameter was given';

    /**
     * |---------------------------------------------------
     * | Generic error message
     * |---------------------------------------------------
     */
    public const GENERIC_ERROR = 'Something went wrong, please try again later';
}
