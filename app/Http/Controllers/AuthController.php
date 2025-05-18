<?php

namespace App\Http\Controllers;

use App\Application\DTOs\LoginDTO;
use App\Application\UseCases\Users\LoginUserUseCase;
use App\Application\UseCases\Users\LogoutUserUseCase;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function login(Request $request, LoginUserUseCase $useCase)
    {
        $dto = new LoginDTO(
            $request->input('email'),
            $request->input('password')
        );

        try {
            return response()->json($useCase->execute($dto));
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    public function logout(LogoutUserUseCase $useCase)
    {
        $useCase->execute();
        return response()->json(['message' => 'Logout realizado com sucesso']);
    }
}
