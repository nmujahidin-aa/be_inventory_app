<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\{TokenExpiredException, TokenInvalidException, JWTException};


class JwtMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return response()->json(['success' => false, 'message' => 'User tidak ditemukan.'], 401);
            }

            if (!$user->is_active) {
                return response()->json(['success' => false, 'message' => 'Akun kamu telah dinonaktifkan.'], 403);
            }

        } catch (TokenExpiredException $e) {
            return response()->json(['success' => false, 'message' => 'Token expired. Silakan login kembali.'], 401);
        } catch (TokenInvalidException $e) {
            return response()->json(['success' => false, 'message' => 'Token tidak valid.'], 401);
        } catch (JWTException $e) {
            return response()->json(['success' => false, 'message' => 'Token tidak ditemukan.'], 401);
        }

        return $next($request);
    }
}
