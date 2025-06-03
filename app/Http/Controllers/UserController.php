<?php

namespace App\Http\Controllers;

use App\Application\DTOs\ChangePasswordDTO;
use App\Application\DTOs\RegisterUserDTO;
use App\Application\DTOs\UpdateUserDTO;
use App\Application\UseCases\Users\ChangePasswordUseCase;
use App\Application\UseCases\Users\DeleteUserUseCase;
use App\Application\UseCases\Users\GetUserByIdUseCase;
use App\Application\UseCases\Users\ListAllUsersUseCase;
use App\Application\UseCases\Users\RegisterUserUseCase;
use App\Application\UseCases\Users\UpdateUserUseCase;
use App\Domain\Users\Entities\User;
use App\Http\Requests\UpdateUserValidator;
use App\Http\Resources\UserResource;
use App\Infrastructure\Http\Validators\Users\ChangePasswordValidator;
use App\Infrastructure\Http\Validators\Users\RegisterUserValidator;
use Illuminate\Http\Request;

/**
 * @group Usuários
 *
 * Endpoints para gerenciamento de usuários (CRUD, alteração de senha).
 */
class UserController extends Controller
{
    /**
     * Registrar Novo Usuário
     *
     * Cria um novo usuário com role padrão "user".
     *
     * @bodyParam name string required Nome completo. Example: João Silva
     * @bodyParam email string required Email válido. Example: joao@exemplo.com
     * @bodyParam cpf string required CPF (formato: 123.456.789-00). Example: 123.456.789-00
     * @bodyParam password string required Senha (mínimo 6 caracteres). Example: senha123
     * @bodyParam password_confirmation string required Confirmação da senha. Example: senha123
     *
     * @response 201 {
     *   "id": 1,
     *   "name": "João Silva",
     *   "email": "joao@exemplo.com",
     *   "cpf": "123.456.789-00",
     *   "role": "user",
     *   "created_at": "2024-05-20T00:00:00.000000Z"
     * }
     * @response 400 {
     *   "error": "CPF já cadastrado"
     * }
     * @response 422 {
     *   "errors": {
     *     "name": ["O campo nome é obrigatório"],
     *     "email": ["Formato de email inválido"]
     *   }
     * }
     */
    public function register(RegisterUserValidator $request, RegisterUserUseCase $useCase)
    {
        try {
            $validated = $request->validated();
            $dto = new RegisterUserDTO(...$validated);

            $user = $useCase->execute($dto);
            return response()->json($user, 201);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Alterar Senha do Usuário
     *
     * Atualiza a senha do usuário autenticado.
     *
     * @authenticated
     * @header Authorization Bearer {token}
     * @bodyParam current_password string required Senha atual. Example: senha123
     * @bodyParam new_password string required Nova senha (diferente da atual). Example: novaSenha456
     *
     * @response 200 {
     *   "message": "Senha alterada com sucesso"
     * }
     * @response 401 {
     *   "error": "Senha atual incorreta"
     * }
     * @response 403 {
     *   "error": "Acesso não autorizado"
     * }
     */
    public function changePassword(ChangePasswordValidator $request, ChangePasswordUseCase $useCase)
    {
        try {
            $validated = $request->validated();
            $dto = new ChangePasswordDTO(...$validated);

            $useCase->execute(auth()->user(), $dto);
            return response()->json(['message' => 'Senha alterada com sucesso']);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * Obter Detalhes do Usuário
     *
     * Retorna os detalhes de um usuário específico.
     *
     * @authenticated
     * @urlParam id integer required ID do usuário. Example: 1
     *
     * @response 200 {
     *   "id": 1,
     *   "name": "João Silva",
     *   "email": "joao@exemplo.com",
     *   "cpf": "123.456.789-00",
     *   "role": "user",
     *   "created_at": "2024-05-20T00:00:00.000000Z"
     * }
     * @response 404 {
     *   "error": "Usuário não encontrado"
     * }
     */
    public function show(int $id, GetUserByIdUseCase $useCase)
    {
        $user = $useCase->execute($id);

        if (!$user) {
            return response()->json(['error' => 'Usuário não encontrado'], 404);
        }

        return new UserResource($user);
    }

    /**
     * Listar Todos Usuários (Admin)
     *
     * Lista todos os usuários registrados (apenas administradores).
     *
     * @authenticated
     * @header Authorization Bearer {token}
     *
     * @response 200 [{
     *   "id": 1,
     *   "name": "Admin",
     *   "email": "admin@exemplo.com",
     *   "role": "admin"
     * }]
     * @response 403 {
     *   "error": "Acesso negado: apenas administradores"
     * }
     */
    public function index(Request $request, ListAllUsersUseCase $useCase)
    {
        try {
            $users = $useCase->execute(auth()->user());
            return UserResource::collection($users);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 403);
        }
    }

    /**
     * Atualizar Usuário
     *
     * Atualiza informações do usuário (exceto senha).
     *
     * @authenticated
     * @header Authorization Bearer {token}
     * @urlParam id integer required ID do usuário. Example: 1
     * @bodyParam name string Nome. Example: João Silva Alterado
     * @bodyParam email string Email. Example: novojoao@exemplo.com
     * @bodyParam cpf string CPF. Example: 987.654.321-00
     *
     * @response 200 {
     *   "id": 1,
     *   "name": "João Silva Alterado",
     *   "email": "novojoao@exemplo.com",
     *   "cpf": "987.654.321-00",
     *   "role": "user"
     * }
     * @response 403 {
     *   "error": "Acesso negado: você só pode atualizar seu próprio perfil"
     * }
     */
    public function update(UpdateUserValidator $request, int $id, UpdateUserUseCase $useCase)
    {
        try {
            $validated = $request->validated();
            $validated['id'] = $id;
            $dto = new UpdateUserDTO(...$validated);

            $updatedUser = $useCase->execute($id, $dto);
            return new UserResource($updatedUser);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Excluir Usuário (Soft Delete - Admin)
     *
     * Remove um usuário do sistema (soft delete, apenas administradores).
     *
     * @authenticated
     * @header Authorization Bearer {token}
     * @urlParam id integer required ID do usuário. Example: 1
     *
     * @response 204
     * @response 403 {
     *   "error": "Acesso negado: apenas administradores"
     * }
     * @response 404 {
     *   "error": "Usuário não encontrado"
     * }
     */
    public function destroy(int $id, DeleteUserUseCase $useCase) {
        $targetUser = User::findOrFail($id);
        $useCase->execute(auth()->user(), $targetUser);
        return response()->json(null, 204);
    }
}
