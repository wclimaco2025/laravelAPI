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
     * Get daily user registration statistics.
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
     * Get weekly user registration statistics.
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
     * Get monthly user registration statistics.
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
