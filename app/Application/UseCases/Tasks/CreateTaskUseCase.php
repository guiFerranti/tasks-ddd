<?php

namespace App\Application\UseCases\Tasks;

use App\Application\DTOs\CreateTaskDTO;
use App\Domain\Tasks\Entities\Task;
use App\Domain\Tasks\Repositories\TaskRepositoryInterface;
use App\Domain\Users\Entities\User;

class CreateTaskUseCase
{
    public function __construct(
        private TaskRepositoryInterface $taskRepository
    ) {}

    public function execute($creator, CreateTaskDTO $dto): Task
    {
        return $this->taskRepository->create([
            'title' => $dto->title,
            'description' => $dto->description,
            'status' => $dto->status->value,
            'created_by' => $creator->id,
            'assigned_to' => $dto->assignedTo->id,
        ]);
    }
}
