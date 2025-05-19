<?php

namespace App\Infrastructure\Http\Validators\Users;

use Illuminate\Foundation\Http\FormRequest;

class ChangePasswordValidator extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6|different:current_password',
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.required' => 'A senha atual é obrigatória',
            'current_password.string' => 'A senha atual deve ser um texto',

            'new_password.required' => 'A nova senha é obrigatória',
            'new_password.string' => 'A nova senha deve ser um texto',
            'new_password.min' => 'A nova senha deve ter pelo menos :min caracteres',
            'new_password.different' => 'A nova senha deve ser diferente da senha atual',
        ];
    }
}
