<?php

namespace App\DTO;

abstract class DTO
{
    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
