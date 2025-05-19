<?php

namespace App\Http\Controllers;

use App\Application\DTOs\ChangePasswordDTO;
use App\Application\DTOs\RegisterUserDTO;
use App\Application\UseCases\Users\ChangePasswordUseCase;
use App\Application\UseCases\Users\DeleteUserUseCase;
use App\Application\UseCases\Users\GetUserByIdUseCase;
use App\Application\UseCases\Users\ListAllUsersUseCase;
use App\Application\UseCases\Users\RegisterUserUseCase;
use App\Application\UseCases\Users\UpdateUserUseCase;
use App\Domain\Users\Entities\User;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function register(Request $request, RegisterUserUseCase $useCase)
    {
        $dto = new RegisterUserDTO(
            $request->input('name'),
            $request->input('email'),
            $request->input('cpf'),
            $request->input('password')
        );

        try {
            $user = $useCase->execute($dto);
            return response()->json($user, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function changePassword(Request $request, ChangePasswordUseCase $useCase)
    {
        $dto = new ChangePasswordDTO(
            $request->input('current_password'),
            $request->input('new_password')
        );

        try {
            $useCase->execute(auth()->user(), $dto);
            return response()->json(['message' => 'Senha alterada com sucesso']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    public function show(int $id, GetUserByIdUseCase $useCase)
    {
        $user = $useCase->execute($id);

        if (!$user) {
            return response()->json(['error' => 'Usuário não encontrado'], 404);
        }

        return new UserResource($user);
    }

    public function index(Request $request, ListAllUsersUseCase $useCase)
    {
        try {
            $users = $useCase->execute(auth()->user());
            return UserResource::collection($users);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 403);
        }
    }

    public function update(Request $request, int $id, UpdateUserUseCase $useCase) {
        $user = User::findOrFail($id);
        $updatedUser = $useCase->execute($user, $request->all());
        return new UserResource($updatedUser);
    }

    public function destroy(int $id, DeleteUserUseCase $useCase) {
        $targetUser = User::findOrFail($id);
        $useCase->execute(auth()->user(), $targetUser);
        return response()->json(null, 204);
    }
}
