<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserValidator extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('id');

        return [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $userId,
            'cpf' => 'sometimes|string|size:14|unique:users,cpf,' . $userId,
        ];
    }

    public function messages(): array
    {
        return [
            'name.string' => 'O nome deve ser uma sequência de caracteres.',
            'name.max' => 'O nome não pode ter mais que 255 caracteres.',

            'email.email' => 'Informe um e-mail válido.',
            'email.unique' => 'Este e-mail já está em uso.',

            'cpf.string' => 'O CPF deve ser uma sequência de caracteres.',
            'cpf.size' => 'O CPF deve ter 14 caracteres com formatação.',
            'cpf.unique' => 'Este CPF já está em uso.',
        ];
    }
}
