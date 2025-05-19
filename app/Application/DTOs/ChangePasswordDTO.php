<?php

namespace App\Application\DTOs;

class ChangePasswordDTO extends BaseDTO
{
    public function __construct(
        public readonly string $current_password,
        public readonly string $new_password
    ) {}
}
