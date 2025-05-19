<?php

namespace App\Infrastructure\Http\Validators\Tasks;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Domain\Tasks\Enums\TaskStatus;

class CreateTaskValidator extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
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

    public function messages(): array
    {
        return [
            'title.required' => 'O título da tarefa é obrigatório',
            'title.string' => 'O título deve ser um texto',
            'title.max' => 'O título não pode exceder :max caracteres',

            'description.required' => 'A descrição é obrigatória',
            'description.string' => 'A descrição deve ser um texto',
            'description.max' => 'A descrição não pode exceder :max caracteres',

            'status.required' => 'O status da tarefa é obrigatório',
            'status.Illuminate\Validation\Rules\Enum' => 'Status inválido',

            'assigned_to.required' => 'O responsável pela tarefa é obrigatório',
            'assigned_to.exists' => 'O usuário atribuído não foi encontrado',
        ];
    }
}
