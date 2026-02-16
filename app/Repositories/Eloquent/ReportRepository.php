<?php

namespace App\Repositories\Eloquent;

use App\Models\Report;
use App\Repositories\Contracts\ReportRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ReportRepository extends BaseRepository implements ReportRepositoryInterface
{
    public function __construct(Report $model)
    {
        parent::__construct($model);
    }

    public function getByUser(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->where('user_id', $userId)
            ->with(['user', 'attachments'])
            ->latest()
            ->paginate($perPage);
    }

    public function getByStatus(string $status, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->where('status', $status)
            ->with(['user', 'attachments'])
            ->latest()
            ->paginate($perPage);
    }

    public function getByRiskLevel(string $riskLevel, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->where('ai_risk_level', $riskLevel)
            ->with(['user', 'attachments'])
            ->latest()
            ->paginate($perPage);
    }

    public function getWithFilters(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->with(['user', 'attachments']);

        if (! empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (! empty($filters['role'])) {
            $query->whereHas('user.roles', function ($q) use ($filters) {
                $q->where('name', $filters['role']);
            });
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        if (! empty($filters['risk_level'])) {
            $query->where('ai_risk_level', $filters['risk_level']);
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%");
            });
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('incident_date', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('incident_date', '<=', $filters['date_to']);
        }

        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortDir = $filters['sort_dir'] ?? 'desc';
        $query->orderBy($sortBy, $sortDir);

        return $query->paginate($perPage);
    }

    public function countByStatus(): array
    {
        return $this->model
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();
    }

    public function countByRiskLevel(): array
    {
        return $this->model
            ->whereNotNull('ai_risk_level')
            ->selectRaw('ai_risk_level, COUNT(*) as count')
            ->groupBy('ai_risk_level')
            ->pluck('count', 'ai_risk_level')
            ->toArray();
    }

    public function getTotalCount(): int
    {
        return $this->model->count();
    }

    public function getCountByUser(int $userId): int
    {
        return $this->model->where('user_id', $userId)->count();
    }

    public function getTopReporters(int $limit = 5): array
    {
        return $this->model
            ->select('user_id', \DB::raw('COUNT(*) as report_count'))
            ->with('user:id,name')
            ->groupBy('user_id')
            ->orderByDesc('report_count')
            ->limit($limit)
            ->get()
            ->map(fn ($item) => [
                'user_name' => $item->user->name ?? 'Unknown',
                'user_id' => $item->user_id,
                'report_count' => $item->report_count,
            ])
            ->toArray();
    }
}
