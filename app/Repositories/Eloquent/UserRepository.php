<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    public function findByEmail(string $email): ?User
    {
        return $this->model->where('email', $email)->first();
    }

    public function getWithRoles(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->with('roles');

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if (! empty($filters['role'])) {
            $role = $filters['role'];
            $query->whereHas('roles', function ($q) use ($role) {
                $q->where('slug', $role)->orWhere('name', $role);
            });
        }

        if (isset($filters['is_active']) && $filters['is_active'] !== '' && $filters['is_active'] !== null) {
            $query->where('is_active', (bool) $filters['is_active']);
        }

        return $query->latest()->paginate($perPage);
    }

    public function assignRole(int $userId, int $roleId): void
    {
        $user = $this->model->findOrFail($userId);
        $user->roles()->syncWithoutDetaching([$roleId]);
    }

    public function syncRoles(int $userId, array $roleIds): void
    {
        $user = $this->model->findOrFail($userId);
        $user->roles()->sync($roleIds);
    }
}
