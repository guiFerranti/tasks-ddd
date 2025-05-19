<?php

namespace App\Infrastructure\Http\Validators\Tasks;

use App\Domain\Tasks\Enums\TaskStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Domain\Tasks\Enums\TaskStatus as TaskStatusEnum;

class UpdateTaskValidator extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string|max:1000',
            'status' => [
                'sometimes',
                Rule::enum(TaskStatusEnum::class)
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'title.string' => 'O título deve ser um texto',
            'title.max' => 'O título não pode exceder :max caracteres',

            'description.string' => 'A descrição deve ser um texto',
            'description.max' => 'A descrição não pode exceder :max caracteres',

            'status.Illuminate\Validation\Rules\Enum' => 'Status inválido. Valores aceitos: '.implode(', ', TaskStatusEnum::values()),
        ];
    }
}
