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

    public function findByCpf(string $cpf): ?User
    {
        return User::where('cpf', $cpf)->first();
    }

    public function create(array $data): User
    {
        return User::create($data);
    }

    public function updatePassword(User $user, string $password): void
    {
        $user->update(['password' => bcrypt($password)]);
    }

    public function findById(int $id): ?User
    {
        return User::find($id);
    }

    public function findAll()
    {
        return User::all();
    }

    public function findAllPaginated(int $perPage)
    {
        return User::paginate($perPage);
    }

    public function update(User $user, array $data): User
    {
        $user->update($data);
        return $user->fresh();
    }
}
