<?php

namespace App\Application\DTOs;

class RegisterUserDTO extends BaseDTO
{
    public function __construct(
        public readonly string $name,
        public readonly string $email,
        public readonly string $cpf,
        public readonly string $password
    ) {}
}
