<?php

namespace App\Infrastructure\Http\Validators\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginValidator extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'O e-mail é obrigatório',
            'email.email' => 'Informe um e-mail válido',

            'password.required' => 'A senha é obrigatória',
            'password.string' => 'A senha deve ser um texto',
            'password.min' => 'A senha deve ter pelo menos :min caracteres',
        ];
    }
}
