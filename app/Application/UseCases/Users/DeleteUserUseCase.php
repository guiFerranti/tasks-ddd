<?php

namespace App\Application\UseCases\Users;

use App\Domain\Users\Entities\User;

class DeleteUserUseCase {
    public function execute(User $admin, User $targetUser) {
        if ($admin->role !== 'admin') {
            throw new \Exception("Acesso negado: apenas administradores podem excluir usuários", 403);
        }
        $targetUser->delete();
    }
}
