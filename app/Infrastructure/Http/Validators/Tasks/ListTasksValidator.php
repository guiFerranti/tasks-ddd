<?php

namespace App\Infrastructure\Http\Validators\Tasks;

use Illuminate\Foundation\Http\FormRequest;
use App\Domain\Tasks\Enums\TaskStatus;
use Illuminate\Validation\Rule;

class ListTasksValidator extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'assigned_to' => 'sometimes|integer|exists:users,id',
            'status' => [
                'sometimes',
                Rule::enum(TaskStatus::class)
            ],
            'created_after' => 'sometimes|date_format:Y-m-d',
        ];
    }

    public function messages(): array
    {
        return [
            'assigned_to.integer' => 'O ID do responsável deve ser um número inteiro',
            'assigned_to.exists' => 'O usuário responsável não foi encontrado',

            'status.Illuminate\Validation\Rules\Enum' => 'Status inválido. Valores aceitos: '.implode(',', TaskStatus::values()),

            'created_after.date_format' => 'A data deve estar no formato AAAA-MM-DD',
        ];
    }
}
