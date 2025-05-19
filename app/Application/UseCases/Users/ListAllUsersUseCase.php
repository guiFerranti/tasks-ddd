<?php

namespace App\Application\UseCases\Users;

use App\Domain\Users\Entities\User;
use App\Domain\Users\Repositories\UserRepositoryInterface;

class ListAllUsersUseCase
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    public function execute(User $adminUser)
    {
        // Verifica se é admin
        if ($adminUser->role !== \App\Domain\Users\Enums\UserRole::ADMIN->value) {
            throw new \Exception("Acesso negado: apenas administradores", 403);
        }

        return $this->userRepository->findAll();
    }
}
