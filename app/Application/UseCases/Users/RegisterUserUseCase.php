<?php

namespace App\Application\UseCases\Users;

use App\Application\DTOs\RegisterUserDTO;
use App\Domain\Users\Entities\User;
use App\Domain\Users\Enums\UserRole;
use App\Domain\Users\Repositories\UserRepositoryInterface;

class RegisterUserUseCase
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    public function execute(RegisterUserDTO $dto): User
    {
        return $this->userRepository->create([
            'name' => $dto->name,
            'email' => $dto->email,
            'cpf' => $dto->cpf,
            'password' => bcrypt($dto->password),
            'role' => UserRole::USER->value,
        ]);
    }
}
