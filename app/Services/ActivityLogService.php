<?php

namespace App\Services;

use App\Repositories\Contracts\ActivityLogRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ActivityLogService
{
    public function __construct(
        protected ActivityLogRepositoryInterface $activityLogRepository,
    ) {}

    public function log(string $action, ?Model $model = null, ?string $description = null, ?array $changes = null): void
    {
        $this->activityLogRepository->log([
            'user_id' => Auth::id(),
            'action' => $action,
            'model_type' => $model ? get_class($model) : null,
            'model_id' => $model?->getKey(),
            'description' => $description,
            'changes' => $changes,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    public function getAll(int $perPage = 25)
    {
        return $this->activityLogRepository->getAll($perPage);
    }

    public function getByUser(int $userId, int $perPage = 25)
    {
        return $this->activityLogRepository->getByUser($userId, $perPage);
    }
}
