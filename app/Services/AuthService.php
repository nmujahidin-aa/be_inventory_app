<?php

namespace App\Services;

use App\Models\User;
use App\Interfaces\UserRepositoryInterface;
use App\Http\Resources\Auth\UserProfileResource;
use Illuminate\Support\Facades\{Hash, Password};
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * Class AuthService
 * @package App\Services
 */
class AuthService
{
    public function __construct( private UserRepositoryInterface $userRepo){}

    public function login(array $credentials): array
    {
        $user = $this->userRepo->findByEmail($credentials['email']);

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password salah.'],
            ]);
        }

        if (!$user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['Akun kamu telah dinonaktifkan. Hubungi admin.'],
            ]);
        }

        $token = JWTAuth::fromUser($user);

        return [
            'token'      => $token,
            'token_type' => 'bearer',
            'expires_in' => config('jwt.ttl') * 60,
            'user'       => new UserProfileResource($user->load('roles')),
        ];
    }

    public function logout(): void
    {
        JWTAuth::invalidate(JWTAuth::getToken());
    }

    public function refresh(): array
    {
        $token = JWTAuth::refresh(JWTAuth::getToken());
        return [
            'token'      => $token,
            'token_type' => 'bearer',
            'expires_in' => config('jwt.ttl') * 60,
        ];
    }

    public function me(): UserProfileResource
    {
        $user = JWTAuth::parseToken()->authenticate();
        return new UserProfileResource($user->load('roles'));
    }

    public function forgotPassword(string $email): void
    {
        $status = Password::sendResetLink(['email' => $email]);

        if ($status !== Password::RESET_LINK_SENT) {
            throw ValidationException::withMessages(['email' => ['Email tidak ditemukan.']]);
        }
    }

    public function resetPassword(array $data): void
    {
        $status = Password::reset(
            $data,
            function (User $user, string $password) {
                $user->forceFill(['password' => Hash::make($password)])->save();
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages(['token' => ['Token reset password tidak valid atau sudah expired.']]);
        }
    }
}
