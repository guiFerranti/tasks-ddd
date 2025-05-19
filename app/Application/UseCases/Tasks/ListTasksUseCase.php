<?php

namespace App\Application\UseCases\Tasks;

use App\Application\DTOs\ListTasksDTO;
use App\Domain\Tasks\Repositories\TaskRepositoryInterface;

class ListTasksUseCase
{
    public function __construct(
        private TaskRepositoryInterface $taskRepository
    ) {}

    public function execute(ListTasksDTO $filters)
    {
        return $this->taskRepository->listTasks([
            'assignedTo' => $filters->assignedTo,
            'status' => $filters->status?->value,
            'createdAfter' => $filters->createdAfter,
        ]);
    }
}
