<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\StatsService;
use Illuminate\Http\JsonResponse;

class StatsController extends Controller
{
    protected $statsService;

    public function __construct(StatsService $statsService)
    {
        $this->middleware('jwt');
        $this->statsService = $statsService;
    }

    /**
     * @OA\Get(
     *     path="/api/stats/daily",
     *     summary="Estadísticas diarias de registro",
     *     description="Retorna el número de usuarios registrados por día (requiere autenticación)",
     *     operationId="getDailyStats",
     *     tags={"Estadísticas"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Estadísticas diarias obtenidas exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="date", type="string", format="date", example="2024-11-12"),
     *                     @OA\Property(property="count", type="integer", example=15)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autenticado",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Token inválido",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     *
     * @return JsonResponse
     */
    public function daily(): JsonResponse
    {
        try {
            $stats = $this->statsService->getUsersByDay();

            return response()->json([
                'success' => true,
                'data' => $stats
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'SERVER_ERROR',
                    'message' => 'Error al obtener estadísticas diarias'
                ]
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/stats/weekly",
     *     summary="Estadísticas semanales de registro",
     *     description="Retorna el número de usuarios registrados por semana (requiere autenticación)",
     *     operationId="getWeeklyStats",
     *     tags={"Estadísticas"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Estadísticas semanales obtenidas exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="year", type="integer", example=2024),
     *                     @OA\Property(property="week", type="integer", example=46),
     *                     @OA\Property(property="count", type="integer", example=42)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autenticado",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Token inválido",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     *
     * @return JsonResponse
     */
    public function weekly(): JsonResponse
    {
        try {
            $stats = $this->statsService->getUsersByWeek();

            return response()->json([
                'success' => true,
                'data' => $stats
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'SERVER_ERROR',
                    'message' => 'Error al obtener estadísticas semanales'
                ]
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/stats/monthly",
     *     summary="Estadísticas mensuales de registro",
     *     description="Retorna el número de usuarios registrados por mes (requiere autenticación)",
     *     operationId="getMonthlyStats",
     *     tags={"Estadísticas"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Estadísticas mensuales obtenidas exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="year", type="integer", example=2024),
     *                     @OA\Property(property="month", type="integer", example=11),
     *                     @OA\Property(property="count", type="integer", example=127)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autenticado",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Token inválido",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     *
     * @return JsonResponse
     */
    public function monthly(): JsonResponse
    {
        try {
            $stats = $this->statsService->getUsersByMonth();

            return response()->json([
                'success' => true,
                'data' => $stats
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'SERVER_ERROR',
                    'message' => 'Error al obtener estadísticas mensuales'
                ]
            ], 500);
        }
    }
}
