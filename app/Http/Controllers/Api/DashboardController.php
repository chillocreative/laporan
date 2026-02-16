<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReportResource;
use App\Models\User;
use App\Repositories\Contracts\ReportRepositoryInterface;
use App\Services\MonitoringService;
use App\Services\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        protected MonitoringService $monitoringService,
        protected ReportService $reportService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->hasRole('super-admin')) {
            $stats = $this->monitoringService->getDashboardStats();
            $stats['top_reporters'] = app(ReportRepositoryInterface::class)->getTopReporters(5);

            return response()->json([
                'data' => $stats,
            ]);
        }

        if ($user->hasRole('admin')) {
            return response()->json([
                'data' => $this->getAdminDashboardStats(),
            ]);
        }

        // Regular user dashboard
        return response()->json([
            'data' => [
                'reports' => $this->getUserDashboardStats($user->id),
                'my_reports' => $this->reportService->getForUser($user->id, 5),
            ],
        ]);
    }

    protected function getAdminDashboardStats(): array
    {
        $reportStats = $this->reportService->getDashboardStats();
        $totalUsers = User::count();
        $pendingApprovals = User::where('is_active', false)
            ->whereDoesntHave('roles')
            ->count();
        $recentReports = $this->reportService->getAllWithFilters([], 5);
        $topReporters = app(ReportRepositoryInterface::class)->getTopReporters(5);

        return [
            'reports' => $reportStats,
            'total_users' => $totalUsers,
            'pending_approvals' => $pendingApprovals,
            'recent_reports' => ReportResource::collection($recentReports)->response()->getData(true),
            'top_reporters' => $topReporters,
        ];
    }

    protected function getUserDashboardStats(int $userId): array
    {
        return [
            'total' => app(ReportRepositoryInterface::class)->getCountByUser($userId),
        ];
    }
}
