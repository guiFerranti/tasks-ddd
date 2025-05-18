<?php

namespace App\Application\UseCases\Users;

use Tymon\JWTAuth\Facades\JWTAuth;

class LogoutUserUseCase
{
    public function execute(): void
    {
        JWTAuth::invalidate(JWTAuth::getToken());
    }
}
