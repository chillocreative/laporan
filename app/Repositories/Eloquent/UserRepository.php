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

    public function getWithRoles(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->with('roles')->latest()->paginate($perPage);
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
