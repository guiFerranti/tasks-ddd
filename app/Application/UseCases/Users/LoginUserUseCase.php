<?php

namespace App\Application\UseCases\Users;

use App\Application\DTOs\LoginDTO;
use App\Domain\Users\Repositories\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class LoginUserUseCase
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    public function execute(LoginDTO $dto): array
    {
        $user = $this->userRepository->findByEmail($dto->email);

        if (!$user || !Hash::check($dto->password, $user->password)) {
            throw new \Exception('Credenciais inválidas', 401);
        }

        $token = JWTAuth::fromUser($user);
        return ['token' => $token];
    }
}
