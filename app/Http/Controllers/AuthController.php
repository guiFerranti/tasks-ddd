<?php

namespace App\Http\Controllers;

use App\Application\DTOs\LoginDTO;
use App\Application\UseCases\Users\LoginUserUseCase;
use App\Application\UseCases\Users\LogoutUserUseCase;
use App\Infrastructure\Http\Validators\Auth\LoginValidator;
use Illuminate\Http\Request;

/**
 * @group Autenticação
 *
 * Endpoints para gerenciar autenticação de usuários (login/logout).
 */
class AuthController extends Controller
{
    /**
     * Login de Usuário
     *
     * Autentica um usuário e retorna um token JWT.
     *
     * @bodyParam email string required Email do usuário. Example: usuario@exemplo.com
     * @bodyParam password string required Senha (mínimo 6 caracteres). Example: senha123
     *
     * @response 200 {
     *   "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
     *   "refresh_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
     * }
     * @response 401 {
     *   "error": "Credenciais inválidas"
     * }
     * @response 422 {
     *   "errors": {
     *     "email": ["O campo email é obrigatório"],
     *     "password": ["O campo password é obrigatório"]
     *   }
     * }
     */
    public function login(LoginValidator $request, LoginUserUseCase $useCase)
    {
        try {
            $validated = $request->validated();
            $dto = new LoginDTO(...$validated);

            return response()->json($useCase->execute($dto));

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * Logout de Usuário
     *
     * Invalida o token JWT do usuário atual.
     *
     * @authenticated
     * @header Authorization Bearer {token}
     *
     * @response 200 {
     *   "message": "Logout realizado com sucesso"
     * }
     * @response 401 {
     *   "error": "Token inválido"
     * }
     */
    public function logout(LogoutUserUseCase $useCase)
    {
        $useCase->execute();
        return response()->json(['message' => 'Logout realizado com sucesso']);
    }
}
