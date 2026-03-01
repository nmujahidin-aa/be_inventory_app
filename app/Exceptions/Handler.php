<?php
namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $dontFlash = ['current_password', 'password', 'password_confirmation'];

    public function register(): void
    {
        $this->renderable(function (Throwable $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return $this->handleApiException($e, $request);
            }
        });
    }

    private function handleApiException(Throwable $e)
    {
        if ($e instanceof ValidationException) {
            return response()->json(['success' => false, 'message' => 'Validasi gagal.', 'errors' => $e->errors()], 422);
        }
        if ($e instanceof ModelNotFoundException || $e instanceof NotFoundHttpException) {
            return response()->json(['success' => false, 'message' => 'Data tidak ditemukan.'], 404);
        }
        if ($e instanceof AuthenticationException) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated. Silakan login.'], 401);
        }
        if ($e instanceof MethodNotAllowedHttpException) {
            return response()->json(['success' => false, 'message' => 'Method tidak diizinkan.'], 405);
        }
        if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpException) {
            return response()->json(['success' => false, 'message' => $e->getMessage() ?: 'Forbidden.'], $e->getStatusCode());
        }
        return response()->json(['success' => false, 'message' => 'Server error. Silakan coba lagi.', 'debug' => config('app.debug') ? $e->getMessage() : null], 500);
    }
}