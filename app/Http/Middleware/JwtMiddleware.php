<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;

class JwtMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            // Verificar presencia de token en header Authorization Bearer
            if (!$request->bearerToken()) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'UNAUTHORIZED',
                        'message' => 'Token de autenticación no proporcionado'
                    ]
                ], 401);
            }

            // Validar token usando JWTAuth::parseToken()->authenticate()
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'UNAUTHORIZED',
                        'message' => 'Usuario no encontrado'
                    ]
                ], 401);
            }

            // Agregar usuario autenticado al request
            $request->merge(['auth_user' => $user]);

        } catch (TokenExpiredException $e) {
            // Capturar TokenExpiredException y retornar 401
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'TOKEN_EXPIRED',
                    'message' => 'El token de acceso ha expirado'
                ]
            ], 401);

        } catch (TokenInvalidException $e) {
            // Capturar TokenInvalidException y retornar 403
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'TOKEN_INVALID',
                    'message' => 'El token de acceso es inválido'
                ]
            ], 403);

        } catch (JWTException $e) {
            // Capturar JWTException y retornar 401
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'UNAUTHORIZED',
                    'message' => 'Error al procesar el token de autenticación'
                ]
            ], 401);
        }

        return $next($request);
    }
}
