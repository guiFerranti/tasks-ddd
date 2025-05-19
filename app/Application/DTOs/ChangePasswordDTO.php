<?php

namespace App\Application\DTOs;

class ChangePasswordDTO extends BaseDTO
{
    public function __construct(
        public readonly string $current_password,
        public readonly string $new_password
    ) {}

    public static function rules(): array
    {
        return [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6|different:current_password',
        ];
    }
}
