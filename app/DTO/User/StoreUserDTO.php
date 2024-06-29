<?php

namespace App\DTO\User;

use App\DTO\DTO;

class StoreUserDTO extends DTO
{
    public function __construct(
        public readonly string $name,
        public readonly string $email,
        public readonly string $password
    ) {}

    public static function from(array $params): self
    {
        return new self(...clean($params));
    }
}
