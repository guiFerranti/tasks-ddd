<?php

namespace App\Application\UseCases\Tasks;

use App\Application\DTOs\ListTasksDTO;
use App\Domain\Tasks\Repositories\TaskRepositoryInterface;

class ListDeletedTasksUseCase
{
    public function __construct(
        private TaskRepositoryInterface $taskRepository
    ) {}

    public function execute(ListTasksDTO $filters)
    {
        return $this->taskRepository->listDeletedTasks([
            'assignedTo' => $filters->assignedTo,
            'status' => $filters->status?->value,
            'createdAfter' => $filters->createdAfter,
        ]);
    }
}
