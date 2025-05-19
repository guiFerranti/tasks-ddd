<?php

namespace App\Infrastructure\Http\Validators\Users;

use Illuminate\Foundation\Http\FormRequest;

class RegisterUserValidator extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'cpf' => 'required|string|size:14|unique:users,cpf',
            'password' => 'required|string|min:6|confirmed',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'O nome é obrigatório.',
            'name.string' => 'O nome deve ser uma sequência de caracteres.',
            'name.max' => 'O nome não pode ter mais que 255 caracteres.',

            'email.required' => 'O e-mail é obrigatório.',
            'email.email' => 'Informe um e-mail válido.',
            'email.unique' => 'Este e-mail já está em uso.',

            'cpf.required' => 'O CPF é obrigatório.',
            'cpf.string' => 'O CPF deve ser uma sequência de caracteres.',
            'cpf.size' => 'O CPF deve ter 14 caracteres com formatação.',
            'cpf.unique' => 'Este CPF já está em uso.',

            'password.required' => 'A senha é obrigatória.',
            'password.string' => 'A senha deve ser uma sequência de caracteres.',
            'password.min' => 'A senha deve ter pelo menos 6 caracteres.',
            'password.confirmed' => 'A confirmação da senha não confere.',
        ];
    }
}
