<?php

namespace App\Domain\Users\Repositories;

use App\Domain\Users\Entities\User;

interface UserRepositoryInterface
{
    public function findByEmail(string $email): ?User;
    public function findByCpf(string $cpf): ?User;
    public function create(array $data): User;
    public function updatePassword(User $user, string $password): void;
}
