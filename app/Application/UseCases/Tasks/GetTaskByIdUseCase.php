<?php

namespace App\Application\UseCases\Tasks;

use App\Domain\Tasks\Entities\Task;
use App\Domain\Tasks\Repositories\TaskRepositoryInterface;

class GetTaskByIdUseCase
{
    public function __construct(
        private TaskRepositoryInterface $taskRepository
    ) {}

    public function execute(int $taskId): ?Task
    {
        return $this->taskRepository->findById($taskId);
    }
}
