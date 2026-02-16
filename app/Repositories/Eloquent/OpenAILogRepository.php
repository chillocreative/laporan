<?php

namespace App\Repositories\Eloquent;

use App\Models\OpenAILog;
use App\Repositories\Contracts\OpenAILogRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class OpenAILogRepository implements OpenAILogRepositoryInterface
{
    public function __construct(protected OpenAILog $model) {}

    public function log(array $data): Model
    {
        $data['created_at'] = $data['created_at'] ?? now();

        return $this->model->create($data);
    }

    public function getAll(int $perPage = 25): LengthAwarePaginator
    {
        return $this->model
            ->with(['user', 'report'])
            ->latest('created_at')
            ->paginate($perPage);
    }

    public function getTodayUsage(): array
    {
        $today = $this->model->whereDate('created_at', today());

        return [
            'total_requests' => $today->count(),
            'total_tokens' => (int) $today->sum('total_tokens'),
            'estimated_cost' => (float) $today->sum('cost_estimate'),
            'avg_response_time' => (int) $today->avg('response_time_ms'),
            'failed_count' => $today->clone()->where('status', 'failed')->count(),
        ];
    }

    public function getByReport(int $reportId): Collection
    {
        return $this->model
            ->where('report_id', $reportId)
            ->latest('created_at')
            ->get();
    }

    public function getFiltered(array $filters, int $perPage = 25): LengthAwarePaginator
    {
        $query = $this->model->with(['user', 'report']);

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query->latest('created_at')->paginate($perPage);
    }
}
