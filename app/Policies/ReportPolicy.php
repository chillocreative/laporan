<?php

namespace App\Policies;

use App\Models\Report;
use App\Models\User;

class ReportPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('reports.view-all')
            || $user->hasPermission('reports.view-own');
    }

    public function view(User $user, Report $report): bool
    {
        if ($user->hasPermission('reports.view-all')) {
            return true;
        }

        return $user->hasPermission('reports.view-own')
            && $report->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('reports.create');
    }

    public function update(User $user, Report $report): bool
    {
        if ($user->hasPermission('reports.edit-any')) {
            return true;
        }

        return $user->hasPermission('reports.edit-own')
            && $report->user_id === $user->id;
    }

    public function delete(User $user, Report $report): bool
    {
        if ($user->hasPermission('reports.delete-any')) {
            return true;
        }

        return $user->hasPermission('reports.delete-own')
            && $report->user_id === $user->id;
    }

    public function updateStatus(User $user, Report $report): bool
    {
        return $user->hasPermission('reports.update-status');
    }
}
