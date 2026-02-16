<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SecurityLogResource;
use App\Repositories\Contracts\SecurityLogRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SecurityLogController extends Controller
{
    public function __construct(protected SecurityLogRepositoryInterface $securityLogRepository) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user->hasPermission('monitoring.security-logs')) {
            abort(403);
        }

        $filters = $request->only(['event_type', 'severity', 'date_from', 'date_to']);
        $logs = $this->securityLogRepository->getFiltered($filters, $request->integer('per_page', 25));

        return response()->json(SecurityLogResource::collection($logs)->response()->getData(true));
    }
}
