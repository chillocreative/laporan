<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OpenAILogResource;
use App\Repositories\Contracts\OpenAILogRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OpenAILogController extends Controller
{
    public function __construct(protected OpenAILogRepositoryInterface $openaiLogRepository) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user->hasPermission('monitoring.ai-logs')) {
            abort(403);
        }

        $filters = $request->only(['status', 'date_from', 'date_to']);
        $logs = $this->openaiLogRepository->getFiltered($filters, $request->integer('per_page', 25));

        return response()->json(OpenAILogResource::collection($logs)->response()->getData(true));
    }

    public function todayUsage(): JsonResponse
    {
        return response()->json([
            'data' => $this->openaiLogRepository->getTodayUsage(),
        ]);
    }
}
