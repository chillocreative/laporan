<?php

namespace App\Repositories\Eloquent;

use App\Models\SecurityLog;
use App\Repositories\Contracts\SecurityLogRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

class SecurityLogRepository implements SecurityLogRepositoryInterface
{
    public function __construct(protected SecurityLog $model) {}

    public function log(array $data): Model
    {
        $data['created_at'] = $data['created_at'] ?? now();

        return $this->model->create($data);
    }

    public function getAll(int $perPage = 25): LengthAwarePaginator
    {
        return $this->model
            ->with('user')
            ->latest('created_at')
            ->paginate($perPage);
    }

    public function getBySeverity(string $severity, int $perPage = 25): LengthAwarePaginator
    {
        return $this->model
            ->with('user')
            ->where('severity', $severity)
            ->latest('created_at')
            ->paginate($perPage);
    }

    public function getRecentSuspicious(int $hours = 24): int
    {
        return $this->model
            ->where('created_at', '>=', now()->subHours($hours))
            ->whereIn('severity', ['high', 'critical'])
            ->count();
    }

    public function getFiltered(array $filters, int $perPage = 25): LengthAwarePaginator
    {
        $query = $this->model->with('user');

        if (! empty($filters['event_type'])) {
            $query->where('event_type', $filters['event_type']);
        }

        if (! empty($filters['severity'])) {
            $query->where('severity', $filters['severity']);
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
