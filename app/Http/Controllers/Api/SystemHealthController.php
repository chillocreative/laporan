<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\MonitoringService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SystemHealthController extends Controller
{
    public function __construct(protected MonitoringService $monitoringService) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user->hasPermission('monitoring.system-health')) {
            abort(403);
        }

        return response()->json([
            'data' => $this->monitoringService->getDashboardStats(),
        ]);
    }
}
