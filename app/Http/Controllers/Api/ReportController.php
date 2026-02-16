<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Report\StoreReportRequest;
use App\Http\Requests\Report\UpdateReportRequest;
use App\Http\Resources\ReportResource;
use App\Models\Report;
use App\Services\OpenAIService;
use App\Services\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(
        protected ReportService $reportService,
        protected OpenAIService $openAIService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $filters = $request->only([
            'status', 'category', 'risk_level', 'search',
            'date_from', 'date_to', 'sort_by', 'sort_dir',
        ]);

        // Non-admin users only see own reports
        if (! $user->hasAnyRole(['super-admin', 'admin'])) {
            $filters['user_id'] = $user->id;
        }

        $reports = $this->reportService->getAllWithFilters(
            $filters,
            $request->integer('per_page', 15)
        );

        return response()->json(ReportResource::collection($reports)->response()->getData(true));
    }

    public function store(StoreReportRequest $request): JsonResponse
    {
        $report = $this->reportService->create(
            $request->safe()->except('attachments'),
            $request->file('attachments', [])
        );

        // Automatically trigger AI analysis if enabled
        if ($this->openAIService->isEnabled()) {
            $this->openAIService->analyzeReport($report);
            $report->refresh();
        }

        return response()->json([
            'message' => 'Report created successfully.',
            'data' => new ReportResource($report),
        ], 201);
    }

    public function show(Report $report): JsonResponse
    {
        $this->authorize('view', $report);

        $report->load(['user', 'attachments']);

        return response()->json([
            'data' => new ReportResource($report),
        ]);
    }

    public function update(UpdateReportRequest $request, Report $report): JsonResponse
    {
        $report = $this->reportService->update(
            $report,
            $request->safe()->except(['attachments', 'status']),
            $request->file('attachments', [])
        );

        return response()->json([
            'message' => 'Report updated successfully.',
            'data' => new ReportResource($report),
        ]);
    }

    public function destroy(Report $report): JsonResponse
    {
        $this->authorize('delete', $report);

        $this->reportService->delete($report);

        return response()->json(['message' => 'Report deleted successfully.']);
    }

    public function updateStatus(Request $request, Report $report): JsonResponse
    {
        $this->authorize('updateStatus', $report);

        $request->validate([
            'status' => ['required', 'string', 'in:pending,under_review,in_progress,resolved,rejected'],
        ]);

        $report = $this->reportService->updateStatus($report, $request->input('status'));

        return response()->json([
            'message' => 'Report status updated.',
            'data' => new ReportResource($report),
        ]);
    }

    public function triggerAnalysis(Request $request, Report $report): JsonResponse
    {
        $user = $request->user();

        if (! $user->hasPermission('ai.trigger')) {
            abort(403, 'You do not have permission to trigger AI analysis.');
        }

        if (! $this->openAIService->isEnabled()) {
            return response()->json([
                'message' => 'AI analysis is currently disabled. Enable it in Settings.',
            ], 422);
        }

        $result = $this->openAIService->analyzeReport($report);

        if ($result) {
            return response()->json([
                'message' => 'AI analysis completed.',
                'status' => 'completed',
                'data' => new ReportResource($report->fresh()),
            ]);
        }

        return response()->json([
            'message' => 'AI analysis failed. Check the AI logs for details.',
            'status' => 'failed',
        ], 500);
    }

    public function analysisStatus(Report $report): JsonResponse
    {
        return response()->json([
            'data' => [
                'analyzed' => $report->isAnalyzed(),
                'analyzed_at' => $report->ai_analyzed_at?->toISOString(),
                'risk_level' => $report->ai_risk_level?->value,
            ],
        ]);
    }
}
