<?php

namespace App\Application\UseCases\Users;

use App\Domain\Users\Entities\User;
use App\Domain\Users\Repositories\UserRepositoryInterface;

class GetUserByIdUseCase
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    public function execute(int $userId): ?User
    {
        return $this->userRepository->findById($userId);
    }
}
