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

    public static function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
            'status' => [
                'required',
                Rule::enum(TaskStatus::class)
            ],
            'assigned_to' => 'required|exists:users,id',
        ];
    }
}
