<?php

namespace App\Application\UseCases\Users;

use App\Domain\Users\Entities\User;
use App\Domain\Users\Enums\UserRole;
use App\Domain\Users\Repositories\UserRepositoryInterface;

class ListAllUsersUseCase
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    public function execute(User $adminUser, int $perPage = 15)
    {
        if ($adminUser->role !== UserRole::ADMIN->value) {
            throw new \Exception("Acesso negado: apenas administradores", 403);
        }

        return $this->userRepository->findAllPaginated($perPage);
    }
}
