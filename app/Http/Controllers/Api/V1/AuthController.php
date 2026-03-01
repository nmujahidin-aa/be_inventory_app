<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use Illuminate\Http\JsonResponse;
use App\Services\AuthService;

class AuthController extends ApiController
{
    public function __construct(private AuthService $service) {}

    public function login(LoginRequest $request): JsonResponse
    {
        return $this->success($this->service->login($request->validated()), 'Login berhasil.');
    }

    public function logout(): JsonResponse
    {
        $this->service->logout();
        return $this->success(null, 'Logout berhasil.');
    }

    public function refresh(): JsonResponse
    {
        return $this->success($this->service->refresh(), 'Token diperbarui.');
    }

    public function me(): JsonResponse
    {
        return $this->success($this->service->me());
    }

    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $this->service->forgotPassword($request->email);
        return $this->success(null, 'Link reset password telah dikirim ke email kamu.');
    }

    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $this->service->resetPassword($request->validated());
        return $this->success(null, 'Password berhasil direset. Silakan login kembali.');
    }
}
