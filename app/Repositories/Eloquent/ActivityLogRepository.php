<?php

namespace App\Repositories\Eloquent;

use App\Models\ActivityLog;
use App\Repositories\Contracts\ActivityLogRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

class ActivityLogRepository implements ActivityLogRepositoryInterface
{
    public function __construct(protected ActivityLog $model) {}

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

    public function getByUser(int $userId, int $perPage = 25): LengthAwarePaginator
    {
        return $this->model
            ->with('user')
            ->where('user_id', $userId)
            ->latest('created_at')
            ->paginate($perPage);
    }

    public function getByAction(string $action, int $perPage = 25): LengthAwarePaginator
    {
        return $this->model
            ->with('user')
            ->where('action', $action)
            ->latest('created_at')
            ->paginate($perPage);
    }

    public function getFiltered(array $filters, int $perPage = 25): LengthAwarePaginator
    {
        $query = $this->model->with('user');

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhere('action', 'like', "%{$search}%");
            });
        }

        if (! empty($filters['user'])) {
            $query->whereHas('user', function ($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['user']}%");
            });
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
