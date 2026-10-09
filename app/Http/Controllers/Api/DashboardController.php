<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReportResource;
use App\Models\MinitMesyuarat;
use App\Models\PenyataKewangan;
use App\Models\Report;
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
            $stats['mpkk'] = $this->getMpkkStats();

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
            'mpkk' => $this->getMpkkStats(),
        ];
    }

    /**
     * Report/financial-statement totals for MPKK users, plus a per-MPKK
     * breakdown (0 for MPKK users who have not submitted any report).
     */
    protected function getMpkkStats(): array
    {
        $mpkkUsers = User::whereHas('roles', fn ($q) => $q->where('slug', 'mpkk'))
            ->withCount(['reports', 'penyataKewangans', 'minitMesyuarats'])
            ->orderBy('name')
            ->get(['id', 'name']);

        $ids = $mpkkUsers->pluck('id');

        return [
            'total_reports' => Report::whereIn('user_id', $ids)->count(),
            'total_penyata_kewangan' => PenyataKewangan::whereIn('user_id', $ids)->count(),
            'total_minit_mesyuarat' => MinitMesyuarat::whereIn('user_id', $ids)->count(),
            'users' => $mpkkUsers->map(fn ($u) => [
                'user_id' => $u->id,
                'user_name' => $u->name,
                'report_count' => $u->reports_count,
                'penyata_kewangan_count' => $u->penyata_kewangans_count,
                'minit_mesyuarat_count' => $u->minit_mesyuarats_count,
            ])->values(),
        ];
    }

    protected function getUserDashboardStats(int $userId): array
    {
        return [
            'total' => app(ReportRepositoryInterface::class)->getCountByUser($userId),
        ];
    }
}
