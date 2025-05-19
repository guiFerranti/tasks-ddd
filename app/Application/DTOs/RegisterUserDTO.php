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

    public static function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'cpf' => 'required|string|size:14|unique:users,cpf',
            'password' => 'required|string|min:6|confirmed',
        ];
    }
}
