<?php

namespace App\DTO\User;

use App\DTO\DTO;

class UpdateUserDTO extends DTO
{
    public function __construct(
        public readonly string $id,
        public readonly ?string $name = null,
        public readonly ?string $email = null,
        public readonly ?string $password = null
    ) {}

    public static function from(array $params): self
    {
        return new self(...clean($params));
    }
}
