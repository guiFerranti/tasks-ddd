<?php

namespace App\Application\DTOs;

use App\Domain\Tasks\Enums\TaskStatus;
use App\Domain\Users\Entities\User;
use Illuminate\Validation\Rule;

class CreateTaskDTO extends BaseDTO
{
    public function __construct(
        public readonly string $title,
        public readonly string $description,
        public readonly TaskStatus $status,
        public readonly User $assignedTo
    ) {}

    public static function fromValidatedData(array $data): self
    {
        return new self(
            title: $data['title'],
            description: $data['description'],
            status: TaskStatus::from($data['status']),
            assignedTo: User::findOrFail($data['assigned_to'])
        );
    }
}
