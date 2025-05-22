<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    protected $dontReport = [
        ValidationException::class,
        BadRequestHttpException::class,
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $exception)
    {
        if ($request->is('api/*') || $request->wantsJson()) {
            return $this->handleApiException($request, $exception);
        }

        return parent::render($request, $exception);
    }

    protected function handleApiException($request, Throwable $exception)
    {
        if ($exception instanceof ValidationException) {
            return $this->invalidJson($request, $exception);
        }

        if ($exception instanceof UnauthorizedHttpException) {
            return $this->handleJwtExceptions($exception);
        }

        if ($exception instanceof AuthenticationException) {
            return response()->json(['error' => 'Não autenticado'], 401);
        }

        if ($exception instanceof ModelNotFoundException || $exception instanceof NotFoundHttpException) {
            return response()->json(['error' => 'Recurso não encontrado'], 404);
        }

        if ($exception instanceof MethodNotAllowedHttpException) {
            return response()->json(['error' => 'Método não permitido'], 405);
        }

        $statusCode = method_exists($exception, 'getStatusCode')
            ? $exception->getStatusCode()
            : 500;

        $errorMessage = $this->getErrorMessage($exception, $statusCode);

        return response()->json(['error' => $errorMessage], $statusCode);
    }

    protected function getErrorMessage(Throwable $exception, int $statusCode): string
    {
        $message = match ($statusCode) {
            401 => 'Não autorizado',
            403 => 'Acesso proibido',
            419 => 'Sessão expirada',
            422 => 'Dados inválidos',
            429 => 'Muitas requisições',
            default => $exception->getMessage() ?: 'Erro interno do servidor',
        };

        return $exception->getMessage() ?: $message;
    }

    protected function invalidJson($request, ValidationException $exception)
    {
        return response()->json([
            'message' => 'Dados inválidos',
            'errors' => $exception->errors(),
        ], $exception->status);
    }

    protected function handleJwtExceptions(UnauthorizedHttpException $exception)
    {
        $message = $exception->getMessage();
        $statusCode = 401;

        return match ($message) {
            'Token not provided' => response()->json(['error' => 'Token não fornecido'], $statusCode),
            'Token expired' => response()->json(['error' => 'Token expirado'], $statusCode),
            'Token has been blacklisted' => response()->json(['error' => 'Token na lista negra'], $statusCode),
            default => response()->json(['error' => 'Token inválido ou malformado'], $statusCode),
        };
    }


}
