<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Users\Entities\User;
use App\Domain\Users\Repositories\UserRepositoryInterface;

class UserEloquentRepository implements UserRepositoryInterface
{
    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }
}
