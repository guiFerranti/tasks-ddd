<?php

namespace App\Application\DTOs;

use Illuminate\Validation\Rule;

class LoginDTO extends BaseDTO
{
    public function __construct(
        public readonly string $email,
        public readonly string $password
    ) {}
}
