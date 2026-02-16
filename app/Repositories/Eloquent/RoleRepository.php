<?php

namespace App\Repositories\Eloquent;

use App\Models\Role;
use App\Repositories\Contracts\RoleRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class RoleRepository extends BaseRepository implements RoleRepositoryInterface
{
    public function __construct(Role $model)
    {
        parent::__construct($model);
    }

    public function findBySlug(string $slug): ?Role
    {
        return $this->model->where('slug', $slug)->first();
    }

    public function syncPermissions(int $roleId, array $permissionIds): void
    {
        $role = $this->model->findOrFail($roleId);
        $role->permissions()->sync($permissionIds);
    }

    public function getWithPermissions(): Collection
    {
        return $this->model->with('permissions')->get();
    }
}
