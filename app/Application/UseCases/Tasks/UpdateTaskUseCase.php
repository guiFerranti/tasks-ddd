<?php

namespace App\Application\UseCases\Tasks;

use App\Application\DTOs\UpdateTaskDTO;
use App\Domain\Tasks\Entities\Task;
use App\Domain\Tasks\Repositories\TaskRepositoryInterface;

class UpdateTaskUseCase
{
    public function __construct(
        private TaskRepositoryInterface $taskRepository
    ) {}

    public function execute(Task $task, UpdateTaskDTO $dto): Task
    {
        $data = array_filter([
            'title' => $dto->title,
            'description' => $dto->description,
            'status' => $dto->status?->value,
        ]);

        return $this->taskRepository->update($task, $data);
    }
}
