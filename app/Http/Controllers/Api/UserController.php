<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateUserRequest;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->middleware('jwt');
        $this->userService = $userService;
    }

    /**
     * Get all users.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            $users = $this->userService->getAllUsers();

            // Transform users to exclude password
            $usersData = $users->map(function ($user) {
                return [
                    'id' => $user->id,
                    'email' => $user->email,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $usersData
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'SERVER_ERROR',
                    'message' => 'Error al obtener usuarios'
                ]
            ], 500);
        }
    }

    /**
     * Get user by ID.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $user = $this->userService->getUserById($id);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $user->id,
                    'email' => $user->email,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                ]
            ], 200);
        } catch (ModelNotFoundException $e) {
            $errorParts = explode(': ', $e->getMessage(), 2);
            $errorCode = count($errorParts) > 1 ? $errorParts[0] : 'USER_NOT_FOUND';
            $errorMessage = count($errorParts) > 1 ? $errorParts[1] : 'Usuario no encontrado';

            return response()->json([
                'success' => false,
                'error' => [
                    'code' => $errorCode,
                    'message' => $errorMessage
                ]
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'SERVER_ERROR',
                    'message' => 'Error al obtener usuario'
                ]
            ], 500);
        }
    }

    /**
     * Update user.
     *
     * @param UpdateUserRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateUserRequest $request, int $id): JsonResponse
    {
        try {
            $user = $this->userService->updateUser($id, $request->validated());

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $user->id,
                    'email' => $user->email,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                ]
            ], 200);
        } catch (ModelNotFoundException $e) {
            $errorParts = explode(': ', $e->getMessage(), 2);
            $errorCode = count($errorParts) > 1 ? $errorParts[0] : 'USER_NOT_FOUND';
            $errorMessage = count($errorParts) > 1 ? $errorParts[1] : 'Usuario no encontrado';

            return response()->json([
                'success' => false,
                'error' => [
                    'code' => $errorCode,
                    'message' => $errorMessage
                ]
            ], 404);
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
     * Delete user.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->userService->deleteUser($id);

            return response()->json(null, 204);
        } catch (ModelNotFoundException $e) {
            $errorParts = explode(': ', $e->getMessage(), 2);
            $errorCode = count($errorParts) > 1 ? $errorParts[0] : 'USER_NOT_FOUND';
            $errorMessage = count($errorParts) > 1 ? $errorParts[1] : 'Usuario no encontrado';

            return response()->json([
                'success' => false,
                'error' => [
                    'code' => $errorCode,
                    'message' => $errorMessage
                ]
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'SERVER_ERROR',
                    'message' => 'Error al eliminar usuario'
                ]
            ], 500);
        }
    }
}
