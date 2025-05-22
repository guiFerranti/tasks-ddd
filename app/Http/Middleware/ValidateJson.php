<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateJson
{
    public function handle(Request $request, Closure $next): Response
    {
        if (
            $request->isJson() &&
            $request->getContent()
        ) {
            json_decode($request->getContent());

            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json([
                    'message' => 'JSON inválido',
                    'erro' => $this->traduzErroJson(json_last_error())
                ], 400);
            }
        }

        return $next($request);
    }

    private function traduzErroJson(int $erro): string
    {
        return match ($erro) {
            JSON_ERROR_DEPTH => 'Profundidade máxima da pilha excedida',
            JSON_ERROR_STATE_MISMATCH => 'Inconsistência ou modo inválido',
            JSON_ERROR_CTRL_CHAR => 'Caractere de controle inesperado encontrado',
            JSON_ERROR_SYNTAX => 'Erro de sintaxe, JSON malformado',
            JSON_ERROR_UTF8 => 'Caracteres UTF-8 malformados, possivelmente codificação incorreta',
            JSON_ERROR_RECURSION => 'Uma ou mais referências recursivas no valor a ser codificado',
            JSON_ERROR_INF_OR_NAN => 'Um ou mais valores NAN ou INF no valor a ser codificado',
            JSON_ERROR_UNSUPPORTED_TYPE => 'Tipo de valor fornecido não é compatível',
            default => 'Erro desconhecido ao decodificar JSON',
        };
    }
}
