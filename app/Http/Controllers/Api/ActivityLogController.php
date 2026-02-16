<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ActivityLogResource;
use App\Repositories\Contracts\ActivityLogRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function __construct(protected ActivityLogRepositoryInterface $activityLogRepository) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user->hasPermission('monitoring.activity-logs')) {
            abort(403);
        }

        $filters = $request->only(['search', 'user', 'date_from', 'date_to']);
        $logs = $this->activityLogRepository->getFiltered($filters, $request->integer('per_page', 25));

        return response()->json(ActivityLogResource::collection($logs)->response()->getData(true));
    }
}
