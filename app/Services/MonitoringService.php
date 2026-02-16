<?php

namespace App\Services;

use App\Repositories\Contracts\OpenAILogRepositoryInterface;
use App\Repositories\Contracts\ReportRepositoryInterface;
use App\Repositories\Contracts\SecurityLogRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

class MonitoringService
{
    public function __construct(
        protected ReportRepositoryInterface $reportRepository,
        protected SecurityLogRepositoryInterface $securityLogRepository,
        protected OpenAILogRepositoryInterface $openaiLogRepository,
    ) {}

    public function getDashboardStats(): array
    {
        return [
            'reports' => $this->getReportStats(),
            'ai_usage' => $this->openaiLogRepository->getTodayUsage(),
            'suspicious_activity' => $this->securityLogRepository->getRecentSuspicious(24),
            'queue' => $this->getQueueStats(),
            'system_health' => $this->getSystemHealth(),
        ];
    }

    protected function getReportStats(): array
    {
        return [
            'total' => $this->reportRepository->getTotalCount(),
            'by_status' => $this->reportRepository->countByStatus(),
            'by_risk_level' => $this->reportRepository->countByRiskLevel(),
        ];
    }

    protected function getQueueStats(): array
    {
        try {
            $pendingJobs = DB::table('jobs')->count();
            $failedJobs = DB::table('failed_jobs')->count();

            return [
                'pending_jobs' => $pendingJobs,
                'failed_jobs' => $failedJobs,
            ];
        } catch (\Exception) {
            return [
                'pending_jobs' => 0,
                'failed_jobs' => 0,
            ];
        }
    }

    protected function getSystemHealth(): array
    {
        return [
            'database' => $this->checkDatabase(),
            'storage' => $this->checkStorage(),
            'queue' => $this->checkQueue(),
        ];
    }

    protected function checkDatabase(): bool
    {
        try {
            DB::connection()->getPdo();

            return true;
        } catch (\Exception) {
            return false;
        }
    }

    protected function checkStorage(): array
    {
        try {
            $totalSpace = disk_total_space(storage_path());
            $freeSpace = disk_free_space(storage_path());

            return [
                'healthy' => $freeSpace > (100 * 1024 * 1024), // > 100MB free
                'total_gb' => round($totalSpace / 1024 / 1024 / 1024, 2),
                'free_gb' => round($freeSpace / 1024 / 1024 / 1024, 2),
                'used_percent' => round(($totalSpace - $freeSpace) / $totalSpace * 100, 1),
            ];
        } catch (\Exception) {
            return ['healthy' => false];
        }
    }

    protected function checkQueue(): bool
    {
        try {
            // Simple check — if we can count jobs, the queue table works
            DB::table('jobs')->count();

            return true;
        } catch (\Exception) {
            return false;
        }
    }
}
