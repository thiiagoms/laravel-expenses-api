<?php

namespace App\Messages;

abstract class BaseMessage
{
    /**
     * |----------------------------
     * | Field messages
     * |----------------------------
     */
    protected const FIELD_REQUIRED = 'The %s is required';

    protected const FIELD_MIN_LENGTH = 'The %s field must have a minimum of %d characters.';

    protected const FIELD_MAX_LENGTH = 'The %s field should not exceed %d characters.';

    protected const FIELD_TYPE = 'The %s field must be a valid %s.';

    /**
     * |----------------------------
     * | Record messages
     * |----------------------------
     */
    protected const RECORD_ALREADY_EXISTS = '%s already exists';
}
