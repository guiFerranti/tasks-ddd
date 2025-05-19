<?php

namespace App\Domain\Users\Repositories;

use App\Domain\Users\Entities\User;

interface UserRepositoryInterface
{
    public function findByEmail(string $email): ?User;
    public function findByCpf(string $cpf): ?User;
    public function create(array $data): User;
    public function updatePassword(User $user, string $password): void;
    public function findById(int $id): ?User;
    public function findAll();
    public function findAllPaginated(int $perPage);
    public function update(User $user, array $data): User;
}
