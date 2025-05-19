<?php

namespace App\Domain\Tasks\Repositories;

use App\Domain\Tasks\Entities\Task;
use App\Domain\Users\Entities\User;

interface TaskRepositoryInterface
{
    public function create(array $data): Task;
    public function update(Task $task, array $data): Task;
    public function delete(Task $task): void;
    public function findById(int $id): ?Task;
    public function getByUserWithFilters(User $user, array $filters);
    public function softDelete(Task $task): void;
    public function listTasks(array $filters);
    public function listDeletedTasks(array $filters);
}
