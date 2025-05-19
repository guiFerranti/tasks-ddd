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

    public static function rules(): array
    {
        return [
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string|max:1000',
            'status' => [
                'required',
                Rule::enum(TaskStatus::class)
            ],
        ];
    }
}
