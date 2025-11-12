<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RefreshTokenRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Register a new user.
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
                    'user' => [
                        'id' => $result['user']->id,
                        'email' => $result['user']->email,
                        'first_name' => $result['user']->first_name,
                        'last_name' => $result['user']->last_name,
                        'created_at' => $result['user']->created_at,
                        'updated_at' => $result['user']->updated_at,
                    ]
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
     * Login user.
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
                    'user' => [
                        'id' => $result['user']->id,
                        'email' => $result['user']->email,
                        'first_name' => $result['user']->first_name,
                        'last_name' => $result['user']->last_name,
                        'created_at' => $result['user']->created_at,
                        'updated_at' => $result['user']->updated_at,
                    ]
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
     * Refresh access token.
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
     * Logout user by revoking refresh token.
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
