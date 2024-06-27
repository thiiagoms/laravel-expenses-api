<?php

namespace App\Enums\User;

enum NameEnum: int
{
    case MIN_LENGTH = 3;
    case MAX_LENGTH = 255;
}
