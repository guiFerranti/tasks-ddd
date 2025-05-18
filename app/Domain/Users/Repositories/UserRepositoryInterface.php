<?php

namespace App\Domain\Users\Repositories;

interface UserRepositoryInterface
{
    public function findByEmail(string $email): ?\App\Domain\Users\Entities\User;
}
