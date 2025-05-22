<?php

namespace App\Application\DTOs;

use App\Domain\Tasks\Enums\TaskStatus;
use Illuminate\Validation\Rule;

class UpdateTaskDTO extends BaseDTO
{
    public function __construct(
        public readonly ?string $title,
        public readonly ?string $description,
        public readonly ?TaskStatus $status
    ) {}

    public static function fromValidatedData(array $data): self
    {
        return new self(
            title: $data['title'] ?? null,
            description: $data['description'] ?? null,
            status: isset($data['status']) ? TaskStatus::from($data['status']) : null
        );
    }
}
