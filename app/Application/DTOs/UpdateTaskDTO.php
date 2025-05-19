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
}
