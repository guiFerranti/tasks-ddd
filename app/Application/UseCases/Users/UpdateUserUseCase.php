<?php

namespace App\Application\UseCases\Users;

use App\Application\DTOs\UpdateUserDTO;
use App\Domain\Users\Entities\User;
use App\Domain\Users\Repositories\UserRepositoryInterface;

class UpdateUserUseCase
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    public function execute(int $userId, UpdateUserDTO $dto): User
    {
        $user = $this->userRepository->findById($userId);

        $updateData = array_filter([
            'name' => $dto->name,
            'email' => $dto->email,
            'cpf' => $dto->cpf,
        ]);

        return $this->userRepository->update($user, $updateData);
    }
}
