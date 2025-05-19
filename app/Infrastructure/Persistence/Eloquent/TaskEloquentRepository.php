<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Tasks\Entities\Task;
use App\Domain\Tasks\Repositories\TaskRepositoryInterface;

class TaskEloquentRepository implements TaskRepositoryInterface
{
    public function create(array $data): Task
    {
        return Task::create($data);
    }

    public function update(Task $task, array $data): Task
    {
        $task->update($data);
        return $task;
    }

    public function delete(Task $task): void
    {
        $task->delete();
    }

    public function findById(int $id): ?Task
    {
        return Task::find($id);
    }

    public function getByUserWithFilters($user, array $filters)
    {
        return Task::query()
            ->where('assigned_to', $user->id)
            ->when(isset($filters['status']), fn($q) => $q->where('status', $filters['status']))
            ->when(isset($filters['createdAfter']), fn($q) => $q->where('created_at', '>=', $filters['createdAfter']))
            ->get();
    }

    public function softDelete(Task $task): void
    {
        if ($task->status !== \App\Domain\Tasks\Enums\TaskStatus::COMPLETED->value) {
            $task->delete();
        } else {
            throw new \Exception("Não é possível excluir tarefas concluídas", 403);
        }
    }

    public function listTasks(array $filters)
    {
        return Task::query()
            ->when(isset($filters['assignedTo']), function ($query) use ($filters) {
                $query->where('assigned_to', $filters['assignedTo']);
            })
            ->when(isset($filters['status']), function ($query) use ($filters) {
                $query->where('status', $filters['status']);
            })
            ->when(isset($filters['createdAfter']), function ($query) use ($filters) {
                $query->whereDate('created_at', '>=', $filters['createdAfter']);
            })
            ->get();
    }

    public function listDeletedTasks(array $filters)
    {
        return Task::onlyTrashed()
        ->when(isset($filters['assignedTo']), function ($query) use ($filters) {
            $query->where('assigned_to', $filters['assignedTo']);
        })
            ->when(isset($filters['status']), function ($query) use ($filters) {
                $query->where('status', $filters['status']);
            })
            ->when(isset($filters['createdAfter']), function ($query) use ($filters) {
                $query->whereDate('created_at', '>=', $filters['createdAfter']);
            })
            ->get();
    }
}
