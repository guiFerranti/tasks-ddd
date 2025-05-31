<?php

namespace App\Application\UseCases\Tasks;

use App\Domain\Tasks\Entities\Task;
use App\Domain\Tasks\Repositories\TaskRepositoryInterface;
use App\Domain\Users\Entities\User;

class DeleteTaskUseCase
{
    public function __construct(
        private TaskRepositoryInterface $taskRepository
    ) {}

    public function execute(User $user, Task $task): void
    {
        if ($user->role !== \App\Domain\Users\Enums\UserRole::ADMIN->value) {
            throw new \Exception("Acesso negado: apenas administradores podem excluir tarefas", 403);
        }

        $this->taskRepository->softDelete($task);
    }
}
