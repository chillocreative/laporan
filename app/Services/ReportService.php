<?php

namespace App\Services;

use App\Events\ReportCreated;
use App\Models\Report;
use App\Repositories\Contracts\ReportRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class ReportService
{
    public function __construct(
        protected ReportRepositoryInterface $reportRepository,
        protected FileUploadService $fileUploadService,
        protected ActivityLogService $activityLogService,
    ) {}

    public function create(array $data, array $files = []): Report
    {
        $data['user_id'] = Auth::id();

        $report = $this->reportRepository->create($data);

        if (! empty($files)) {
            $this->fileUploadService->uploadMultiple($report, $files);
        }

        $this->activityLogService->log('report_created', $report, "Report created: {$report->title}");

        event(new ReportCreated($report));

        return $report->load(['user', 'attachments']);
    }

    public function update(Report $report, array $data, array $files = []): Report
    {
        $report->update($data);

        if (! empty($files)) {
            $this->fileUploadService->uploadMultiple($report, $files);
        }

        $this->activityLogService->log('report_updated', $report, "Report updated: {$report->title}");

        return $report->fresh(['user', 'attachments']);
    }

    public function delete(Report $report): bool
    {
        $this->activityLogService->log('report_deleted', $report, "Report deleted: {$report->title}");

        // Soft delete — attachments preserved for audit
        return $report->delete();
    }

    public function updateStatus(Report $report, string $status): Report
    {
        $oldStatus = $report->status->value;
        $report->update(['status' => $status]);

        $this->activityLogService->log(
            'report_status_changed',
            $report,
            "Report status changed from {$oldStatus} to {$status}"
        );

        return $report->fresh();
    }

    public function getForUser(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->reportRepository->getByUser($userId, $perPage);
    }

    public function getAllWithFilters(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->reportRepository->getWithFilters($filters, $perPage);
    }

    public function getById(int $id): Report
    {
        return $this->reportRepository->findOrFail($id)->load(['user', 'attachments']);
    }

    public function getDashboardStats(): array
    {
        return [
            'total' => $this->reportRepository->getTotalCount(),
            'by_status' => $this->reportRepository->countByStatus(),
            'by_risk_level' => $this->reportRepository->countByRiskLevel(),
        ];
    }
}
