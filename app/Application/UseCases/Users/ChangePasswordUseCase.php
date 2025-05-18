<?php

namespace App\Application\UseCases\Users;

use App\Application\DTOs\ChangePasswordDTO;
use App\Domain\Users\Entities\User;
use App\Domain\Users\Repositories\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;

class ChangePasswordUseCase
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    public function execute(User $user, ChangePasswordDTO $dto): void
    {
        if (!Hash::check($dto->current_password, $user->password)) {
            throw new \Exception('Senha atual incorreta', 401);
        }

        $this->userRepository->updatePassword($user, $dto->new_password);
    }
}
