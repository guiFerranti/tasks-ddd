<?php

namespace App\Application\UseCases\Users;

use App\Domain\Users\Entities\User;

class UpdateUserUseCase {
    public function execute(User $user, array $data) {
        $user->update($data);
        return $user;
    }
}
