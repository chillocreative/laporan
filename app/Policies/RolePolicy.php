<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;

class RolePolicy
{
    public function viewAny(User $user): bool
    {
        // Allow both Super Admins (roles.view) and Admins (users.view-all) to view roles list
        return $user->hasPermission('roles.view') || $user->hasPermission('users.view-all');
    }

    public function view(User $user, Role $role): bool
    {
        // Allow both Super Admins and Admins to view individual roles
        return $user->hasPermission('roles.view') || $user->hasPermission('users.view-all');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('roles.create');
    }

    public function update(User $user, Role $role): bool
    {
        return $user->hasPermission('roles.edit');
    }

    public function delete(User $user, Role $role): bool
    {
        if ($role->is_system) {
            return false;
        }

        return $user->hasPermission('roles.delete');
    }
}
