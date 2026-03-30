<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('users.view-all');
    }

    public function view(User $user, User $model): bool
    {
        if ($user->hasPermission('users.view-all')) {
            return true;
        }

        return $user->id === $model->id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('users.create');
    }

    public function update(User $user, User $model): bool
    {
        // Non-super-admin cannot edit super-admin
        if (! $user->isSuperAdmin() && $model->isSuperAdmin()) {
            return false;
        }

        if ($user->hasPermission('users.edit-any')) {
            return true;
        }

        return $user->id === $model->id;
    }

    public function delete(User $user, User $model): bool
    {
        if ($user->id === $model->id) {
            return false;
        }

        // Non-super-admin cannot delete super-admin
        if (! $user->isSuperAdmin() && $model->isSuperAdmin()) {
            return false;
        }

        return $user->hasPermission('users.delete');
    }

    public function deactivate(User $user, User $model): bool
    {
        if ($user->id === $model->id) {
            return false;
        }

        // Non-super-admin cannot deactivate super-admin
        if (! $user->isSuperAdmin() && $model->isSuperAdmin()) {
            return false;
        }

        return $user->hasPermission('users.deactivate');
    }
}
