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

    /**
     * Create a user, or restore-and-overwrite a soft-deleted user that still
     * owns the same email.
     *
     * The users table has a hard unique index on `email` that does not account
     * for soft deletes, so a plain insert with the email of a trashed account
     * would violate the constraint. Restoring the existing row reuses it (and
     * keeps any historical reports linked) instead.
     */
    public function create(array $data): User
    {
        if (! empty($data['email'])) {
            $trashed = $this->model->onlyTrashed()->where('email', $data['email'])->first();

            if ($trashed) {
                $trashed->restore();
                $trashed->fill($data);
                $trashed->save();

                return $trashed;
            }
        }

        return $this->model->create($data);
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
