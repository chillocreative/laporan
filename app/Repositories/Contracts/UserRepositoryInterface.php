<?php

namespace App\Repositories\Contracts;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface UserRepositoryInterface extends BaseRepositoryInterface
{
    public function findByEmail(string $email): ?User;

    public function getWithRoles(int $perPage = 15): LengthAwarePaginator;

    public function assignRole(int $userId, int $roleId): void;

    public function syncRoles(int $userId, array $roleIds): void;
}
