<?php

namespace App\Repositories\Contracts;

use App\Models\Role;

interface RoleRepositoryInterface extends BaseRepositoryInterface
{
    public function findBySlug(string $slug): ?Role;

    public function syncPermissions(int $roleId, array $permissionIds): void;

    public function getWithPermissions(): \Illuminate\Database\Eloquent\Collection;
}
