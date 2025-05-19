<?php

namespace App\Application\DTOs;

use App\Domain\Tasks\Enums\TaskStatus;

class ListTasksDTO extends BaseDTO
{
    public function __construct(
        public readonly ?int $assignedTo,
        public readonly ?TaskStatus $status,
        public readonly ?string $createdAfter
    ) {}

    public static function rules(): array
    {
        return [
            'assignedTo' => 'sometimes|integer|exists:users,id',
            'status' => 'sometimes|in:pending,in_progress,completed',
            'createdAfter' => 'sometimes|date_format:Y-m-d',
        ];
    }
}
