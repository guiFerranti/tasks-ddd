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

    public static function fromValidatedData(array $data): self
    {
        return new self(
            assignedTo: $data['assigned_to'] ?? null,
            status: isset($data['status']) ? TaskStatus::from($data['status']) : null,
            createdAfter: $data['created_after'] ?? null
        );
    }
}
