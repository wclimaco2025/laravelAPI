<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RefreshTokenRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Info(
 *     title="API de Gestión de Usuarios",
 *     version="1.0.0",
 *     description="API RESTful para gestión de usuarios con autenticación JWT, operaciones CRUD y estadísticas de registro",
 *     @OA\Contact(
 *         email="admin@example.com"
 *     )
 * )
 *
 * @OA\Server(
 *     url=L5_SWAGGER_CONST_HOST,
 *     description="Servidor API"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Ingrese el token JWT en el formato: Bearer {token}"
 * )
 *
 * @OA\Schema(
 *     schema="User",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="email", type="string", format="email", example="usuario@example.com"),
 *     @OA\Property(property="first_name", type="string", example="Juan"),
 *     @OA\Property(property="last_name", type="string", example="Pérez"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2024-11-12T10:30:00.000000Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-11-12T10:30:00.000000Z")
 * )
 *
 * @OA\Schema(
 *     schema="Error",
 *     type="object",
 *     @OA\Property(property="success", type="boolean", example=false),
 *     @OA\Property(
 *         property="error",
 *         type="object",
 *         @OA\Property(property="code", type="string", example="ERROR_CODE"),
 *         @OA\Property(property="message", type="string", example="Mensaje descriptivo del error"),
 *         @OA\Property(property="details", type="array", @OA\Items(type="string"))
 *     )
 * )
 */
class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * @OA\Post(
     *     path="/api/auth/register",
     *     summary="Registrar nuevo usuario",
     *     description="Crea un nuevo usuario en el sistema y retorna tokens de autenticación",
     *     operationId="register",
     *     tags={"Autenticación"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password","first_name","last_name"},
     *             @OA\Property(property="email", type="string", format="email", example="usuario@example.com"),
     *             @OA\Property(property="password", type="string", format="password", minLength=8, example="Password123"),
     *             @OA\Property(property="first_name", type="string", maxLength=100, example="Juan"),
     *             @OA\Property(property="last_name", type="string", maxLength=100, example="Pérez")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Usuario registrado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="access_token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGc..."),
     *                 @OA\Property(property="refresh_token", type="string", example="def502001a2b3c4d5e6f..."),
     *                 @OA\Property(property="user", ref="#/components/schemas/User")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error de validación",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="El email ya está registrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(
     *                 property="error",
     *                 type="object",
     *                 @OA\Property(property="code", type="string", example="USER_ALREADY_EXISTS"),
     *                 @OA\Property(property="message", type="string", example="El email ya está registrado")
     *             )
     *         )
     *     )
     * )
     *
     * @param RegisterRequest $request
     * @return JsonResponse
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $result = $this->authService->register($request->validated());

            return response()->json([
                'success' => true,
                'data' => [
                    'access_token' => $result['access_token'],
                    'refresh_token' => $result['refresh_token'],
                    'user' => new UserResource($result['user'])
                ]
            ], 201);
        } catch (\Exception $e) {
            $statusCode = $e->getCode() ?: 500;
            $errorParts = explode(': ', $e->getMessage(), 2);
            $errorCode = count($errorParts) > 1 ? $errorParts[0] : 'SERVER_ERROR';
            $errorMessage = count($errorParts) > 1 ? $errorParts[1] : $e->getMessage();

            return response()->json([
                'success' => false,
                'error' => [
                    'code' => $errorCode,
                    'message' => $errorMessage
                ]
            ], $statusCode);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/auth/login",
     *     summary="Iniciar sesión",
     *     description="Autentica un usuario con email y contraseña, retorna tokens JWT",
     *     operationId="login",
     *     tags={"Autenticación"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", format="email", example="usuario@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="Password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login exitoso",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="access_token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGc..."),
     *                 @OA\Property(property="refresh_token", type="string", example="def502001a2b3c4d5e6f..."),
     *                 @OA\Property(property="user", ref="#/components/schemas/User")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error de validación",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Credenciales inválidas",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(
     *                 property="error",
     *                 type="object",
     *                 @OA\Property(property="code", type="string", example="INVALID_CREDENTIALS"),
     *                 @OA\Property(property="message", type="string", example="Las credenciales proporcionadas son incorrectas")
     *             )
     *         )
     *     )
     * )
     *
     * @param LoginRequest $request
     * @return JsonResponse
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $result = $this->authService->login(
                $request->input('email'),
                $request->input('password')
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'access_token' => $result['access_token'],
                    'refresh_token' => $result['refresh_token'],
                    'user' => new UserResource($result['user'])
                ]
            ], 200);
        } catch (\Exception $e) {
            $statusCode = $e->getCode() ?: 500;
            $errorParts = explode(': ', $e->getMessage(), 2);
            $errorCode = count($errorParts) > 1 ? $errorParts[0] : 'SERVER_ERROR';
            $errorMessage = count($errorParts) > 1 ? $errorParts[1] : $e->getMessage();

            return response()->json([
                'success' => false,
                'error' => [
                    'code' => $errorCode,
                    'message' => $errorMessage
                ]
            ], $statusCode);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/auth/refresh",
     *     summary="Renovar token de acceso",
     *     description="Genera un nuevo token de acceso usando un refresh token válido",
     *     operationId="refresh",
     *     tags={"Autenticación"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"refresh_token"},
     *             @OA\Property(property="refresh_token", type="string", example="def502001a2b3c4d5e6f...")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Token renovado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="access_token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGc...")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Token expirado o revocado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(
     *                 property="error",
     *                 type="object",
     *                 @OA\Property(property="code", type="string", example="TOKEN_EXPIRED"),
     *                 @OA\Property(property="message", type="string", example="El refresh token ha expirado")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Token inválido",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(
     *                 property="error",
     *                 type="object",
     *                 @OA\Property(property="code", type="string", example="TOKEN_INVALID"),
     *                 @OA\Property(property="message", type="string", example="El refresh token es inválido")
     *             )
     *         )
     *     )
     * )
     *
     * @param RefreshTokenRequest $request
     * @return JsonResponse
     */
    public function refresh(RefreshTokenRequest $request): JsonResponse
    {
        try {
            $accessToken = $this->authService->refreshAccessToken(
                $request->input('refresh_token')
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'access_token' => $accessToken
                ]
            ], 200);
        } catch (\Exception $e) {
            $statusCode = $e->getCode() ?: 500;
            $errorParts = explode(': ', $e->getMessage(), 2);
            $errorCode = count($errorParts) > 1 ? $errorParts[0] : 'SERVER_ERROR';
            $errorMessage = count($errorParts) > 1 ? $errorParts[1] : $e->getMessage();

            return response()->json([
                'success' => false,
                'error' => [
                    'code' => $errorCode,
                    'message' => $errorMessage
                ]
            ], $statusCode);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/auth/logout",
     *     summary="Cerrar sesión",
     *     description="Revoca el refresh token del usuario para cerrar la sesión",
     *     operationId="logout",
     *     tags={"Autenticación"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"refresh_token"},
     *             @OA\Property(property="refresh_token", type="string", example="def502001a2b3c4d5e6f...")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Sesión cerrada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="message", type="string", example="Sesión cerrada exitosamente")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Token inválido",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     *
     * @param RefreshTokenRequest $request
     * @return JsonResponse
     */
    public function logout(RefreshTokenRequest $request): JsonResponse
    {
        try {
            $this->authService->logout($request->input('refresh_token'));

            return response()->json([
                'success' => true,
                'data' => [
                    'message' => 'Sesión cerrada exitosamente'
                ]
            ], 200);
        } catch (\Exception $e) {
            $statusCode = $e->getCode() ?: 500;
            $errorParts = explode(': ', $e->getMessage(), 2);
            $errorCode = count($errorParts) > 1 ? $errorParts[0] : 'SERVER_ERROR';
            $errorMessage = count($errorParts) > 1 ? $errorParts[1] : $e->getMessage();

            return response()->json([
                'success' => false,
                'error' => [
                    'code' => $errorCode,
                    'message' => $errorMessage
                ]
            ], $statusCode);
        }
    }
}
