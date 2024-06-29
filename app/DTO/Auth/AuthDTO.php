<?php

namespace App\DTO\Auth;

use App\DTO\DTO;

class AuthDTO extends DTO
{
    public function __construct(
        public readonly string $email,
        public readonly string $password
    ) {}

    public static function from(array $params): self
    {
        return new self(...clean($params));
    }
}
