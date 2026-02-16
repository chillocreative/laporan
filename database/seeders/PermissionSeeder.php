<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // Reports
            ['name' => 'View All Reports', 'slug' => 'reports.view-all', 'group_name' => 'reports'],
            ['name' => 'View Own Reports', 'slug' => 'reports.view-own', 'group_name' => 'reports'],
            ['name' => 'Create Reports', 'slug' => 'reports.create', 'group_name' => 'reports'],
            ['name' => 'Edit Any Report', 'slug' => 'reports.edit-any', 'group_name' => 'reports'],
            ['name' => 'Edit Own Reports', 'slug' => 'reports.edit-own', 'group_name' => 'reports'],
            ['name' => 'Delete Any Report', 'slug' => 'reports.delete-any', 'group_name' => 'reports'],
            ['name' => 'Delete Own Reports', 'slug' => 'reports.delete-own', 'group_name' => 'reports'],
            ['name' => 'Update Report Status', 'slug' => 'reports.update-status', 'group_name' => 'reports'],

            // Users
            ['name' => 'View All Users', 'slug' => 'users.view-all', 'group_name' => 'users'],
            ['name' => 'Create Users', 'slug' => 'users.create', 'group_name' => 'users'],
            ['name' => 'Edit Any User', 'slug' => 'users.edit-any', 'group_name' => 'users'],
            ['name' => 'Delete Users', 'slug' => 'users.delete', 'group_name' => 'users'],
            ['name' => 'Deactivate Users', 'slug' => 'users.deactivate', 'group_name' => 'users'],

            // Roles
            ['name' => 'View Roles', 'slug' => 'roles.view', 'group_name' => 'roles'],
            ['name' => 'Create Roles', 'slug' => 'roles.create', 'group_name' => 'roles'],
            ['name' => 'Edit Roles', 'slug' => 'roles.edit', 'group_name' => 'roles'],
            ['name' => 'Delete Roles', 'slug' => 'roles.delete', 'group_name' => 'roles'],
            ['name' => 'Assign Roles', 'slug' => 'roles.assign', 'group_name' => 'roles'],

            // Settings
            ['name' => 'View Settings', 'slug' => 'settings.view', 'group_name' => 'settings'],
            ['name' => 'Edit General Settings', 'slug' => 'settings.edit-general', 'group_name' => 'settings'],
            ['name' => 'Edit OpenAI Settings', 'slug' => 'settings.edit-openai', 'group_name' => 'settings'],
            ['name' => 'Edit Captcha Settings', 'slug' => 'settings.edit-captcha', 'group_name' => 'settings'],
            ['name' => 'Edit Branding', 'slug' => 'settings.edit-branding', 'group_name' => 'settings'],

            // Monitoring
            ['name' => 'View Dashboard (All)', 'slug' => 'monitoring.dashboard-all', 'group_name' => 'monitoring'],
            ['name' => 'View Own Dashboard', 'slug' => 'monitoring.dashboard-own', 'group_name' => 'monitoring'],
            ['name' => 'View Activity Logs', 'slug' => 'monitoring.activity-logs', 'group_name' => 'monitoring'],
            ['name' => 'View Security Logs', 'slug' => 'monitoring.security-logs', 'group_name' => 'monitoring'],
            ['name' => 'View AI Logs', 'slug' => 'monitoring.ai-logs', 'group_name' => 'monitoring'],
            ['name' => 'View System Health', 'slug' => 'monitoring.system-health', 'group_name' => 'monitoring'],

            // AI
            ['name' => 'Trigger AI Analysis', 'slug' => 'ai.trigger', 'group_name' => 'ai'],
            ['name' => 'View AI Results', 'slug' => 'ai.view-results', 'group_name' => 'ai'],
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['slug' => $perm['slug']], $perm);
        }

        // Assign permissions to roles
        $this->assignPermissions();
    }

    protected function assignPermissions(): void
    {
        // Super Admin gets ALL permissions (also handled by Gate::before)
        $superAdmin = Role::where('slug', 'super-admin')->first();
        if ($superAdmin) {
            $superAdmin->permissions()->sync(Permission::pluck('id'));
        }

        // Admin permissions
        $admin = Role::where('slug', 'admin')->first();
        if ($admin) {
            $adminPerms = Permission::whereIn('slug', [
                'reports.view-all',
                'reports.view-own',
                'reports.update-status',
                'users.view-all',
                'users.create',
                'users.edit-any',
                'users.deactivate',
                'monitoring.dashboard-all',
                'monitoring.dashboard-own',
                'ai.trigger',
                'ai.view-results',
            ])->pluck('id');
            $admin->permissions()->sync($adminPerms);
        }

        // Regular user permissions (same for User role and all other roles)
        $userPerms = Permission::whereIn('slug', [
            'reports.view-own',
            'reports.create',
            'reports.edit-own',
            'reports.delete-own',
            'monitoring.dashboard-own',
            'ai.view-results',
        ])->pluck('id');

        // Assign to User role
        $user = Role::where('slug', 'user')->first();
        if ($user) {
            $user->permissions()->sync($userPerms);
        }

        // Assign same permissions to ALL other roles (except super-admin and admin)
        $otherRoles = Role::whereNotIn('slug', ['super-admin', 'admin'])->get();
        foreach ($otherRoles as $role) {
            $role->permissions()->sync($userPerms);
        }
    }
}
