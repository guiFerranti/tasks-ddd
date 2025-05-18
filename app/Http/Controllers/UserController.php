<?php

namespace App\Http\Controllers;

use App\Application\DTOs\ChangePasswordDTO;
use App\Application\DTOs\RegisterUserDTO;
use App\Application\UseCases\Users\ChangePasswordUseCase;
use App\Application\UseCases\Users\RegisterUserUseCase;
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
}
