<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Verifica se o usuário é admin
        if ($user && $user->role === 'admin') {
            return $next($request);
        }

        return response()->json(['error' => 'Acesso negado: apenas administradores'], 403);
    }
}
