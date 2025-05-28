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
        if ($userId !== $dto->id) {
            throw new \Exception('IDs não correspondem');
        }

        $user = $this->userRepository->findById($userId);
        if (!$user) {
            throw new \Exception('Usuário não encontrado');
        }

        $updateData = array_filter([
            'name' => $dto->name,
            'email' => $dto->email,
            'cpf' => $dto->cpf,
        ]);

        return $this->userRepository->update($user, $updateData);
    }
}
