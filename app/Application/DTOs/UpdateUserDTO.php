<?php

namespace App\Application\DTOs;

class UpdateUserDTO
{
    public function __construct(
        public readonly int $id,
        public readonly ?string $name = null,
        public readonly ?string $email = null,
        public readonly ?string $cpf = null
    ) {}
}
