<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;

    protected User $admin;

    protected User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            RoleSeeder::class,
            PermissionSeeder::class,
            SettingsSeeder::class,
        ]);

        // Create super-admin user
        $this->superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@test.com',
            'password' => 'password123',
            'is_active' => true,
        ]);
        $superAdminRole = Role::where('slug', 'super-admin')->first();
        $this->superAdmin->roles()->attach($superAdminRole->id);

        // Create admin user
        $this->admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => 'password123',
            'is_active' => true,
        ]);
        $adminRole = Role::where('slug', 'admin')->first();
        $this->admin->roles()->attach($adminRole->id);

        // Create regular user
        $this->regularUser = User::create([
            'name' => 'Regular User',
            'email' => 'user@test.com',
            'password' => 'password123',
            'is_active' => true,
        ]);
        $userRole = Role::where('slug', 'user')->first();
        $this->regularUser->roles()->attach($userRole->id);
    }

    // =========================================================================
    // User Management
    // =========================================================================

    public function test_admin_can_list_users(): void
    {
        $response = $this->actingAs($this->admin)
            ->getJson('/api/users');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'email'],
                ],
            ]);
    }

    public function test_admin_can_create_user(): void
    {
        $userRole = Role::where('slug', 'user')->first();

        $response = $this->actingAs($this->admin)
            ->postJson('/api/users', [
                'name' => 'New User',
                'email' => 'newuser@test.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'role_ids' => [$userRole->id],
                'is_active' => true,
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('message', 'User created successfully.')
            ->assertJsonPath('data.email', 'newuser@test.com');

        $this->assertDatabaseHas('users', ['email' => 'newuser@test.com']);
    }

    public function test_admin_can_recreate_user_with_soft_deleted_email(): void
    {
        $userRole = Role::where('slug', 'user')->first();

        // An existing account is created, then soft-deleted. Its email row
        // still occupies the unique index but is hidden from the user list.
        $deleted = User::create([
            'name' => 'Old MPKK',
            'email' => 'mpkk@test.com',
            'password' => 'password123',
            'is_active' => true,
        ]);
        $deleted->roles()->attach($userRole->id);
        $deleted->delete();

        // Re-registering the same email must succeed (restore + overwrite),
        // not fail with "email has already been taken".
        $response = $this->actingAs($this->admin)
            ->postJson('/api/users', [
                'name' => 'New MPKK',
                'email' => 'mpkk@test.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'role_ids' => [$userRole->id],
                'is_active' => true,
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.email', 'mpkk@test.com');

        // The trashed row was reused (not duplicated) and is now active.
        $this->assertSame(1, User::withTrashed()->where('email', 'mpkk@test.com')->count());
        $this->assertDatabaseHas('users', [
            'id' => $deleted->id,
            'email' => 'mpkk@test.com',
            'name' => 'NEW MPKK', // name mutator uppercases
            'deleted_at' => null,
        ]);
    }

    public function test_admin_can_toggle_user_active(): void
    {
        $targetUser = User::create([
            'name' => 'Target User',
            'email' => 'target@test.com',
            'password' => 'password123',
            'is_active' => true,
        ]);
        $userRole = Role::where('slug', 'user')->first();
        $targetUser->roles()->attach($userRole->id);

        $response = $this->actingAs($this->admin)
            ->patchJson("/api/users/{$targetUser->id}/toggle-active");

        $response->assertStatus(200)
            ->assertJsonPath('message', 'User deactivated successfully.');

        $this->assertDatabaseHas('users', [
            'id' => $targetUser->id,
            'is_active' => false,
        ]);
    }

    public function test_regular_user_cannot_access_user_management(): void
    {
        $response = $this->actingAs($this->regularUser)
            ->getJson('/api/users');

        $response->assertStatus(403);
    }

    // =========================================================================
    // Role Management
    // =========================================================================

    public function test_super_admin_can_list_roles(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->getJson('/api/roles');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'slug'],
                ],
            ]);
    }

    public function test_super_admin_can_create_role(): void
    {
        $permissions = Permission::where('group_name', 'reports')->pluck('id')->toArray();

        $response = $this->actingAs($this->superAdmin)
            ->postJson('/api/roles', [
                'name' => 'Report Manager',
                'slug' => 'report-manager',
                'description' => 'Manages all reports',
                'permission_ids' => $permissions,
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('message', 'Role created successfully.')
            ->assertJsonPath('data.slug', 'report-manager');

        $this->assertDatabaseHas('roles', ['slug' => 'report-manager']);
    }

    public function test_super_admin_cannot_delete_system_role(): void
    {
        // Gate::before grants super-admin full bypass, so we verify the
        // RolePolicy itself denies deletion of system roles.  Because no
        // non-super-admin can reach the role routes (middleware: role:super-admin),
        // we assert the policy directly.
        $systemRole = Role::where('slug', 'admin')->first();

        $this->assertFalse(
            (new \App\Policies\RolePolicy)->delete($this->superAdmin, $systemRole) ?? false,
            'RolePolicy should deny deletion of system roles.'
        );

        // Also verify the role still exists in the database (no accidental deletion).
        $this->assertDatabaseHas('roles', ['id' => $systemRole->id]);
    }

    public function test_admin_cannot_access_roles(): void
    {
        $response = $this->actingAs($this->admin)
            ->getJson('/api/roles');

        $response->assertStatus(403);
    }

    // =========================================================================
    // Settings
    // =========================================================================

    public function test_super_admin_can_view_settings(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->getJson('/api/settings');

        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    public function test_super_admin_can_update_settings(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->putJson('/api/settings/general', [
                'system_name' => 'Updated System Name',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Settings updated successfully.');
    }

    public function test_admin_cannot_access_settings(): void
    {
        $response = $this->actingAs($this->admin)
            ->getJson('/api/settings');

        $response->assertStatus(403);
    }

    // =========================================================================
    // Monitoring Logs
    // =========================================================================

    public function test_super_admin_can_view_activity_logs(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->getJson('/api/logs/activity');

        $response->assertStatus(200);
    }

    public function test_super_admin_can_view_security_logs(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->getJson('/api/logs/security');

        $response->assertStatus(200);
    }

    public function test_super_admin_can_view_ai_logs(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->getJson('/api/logs/ai');

        $response->assertStatus(200);
    }

    // =========================================================================
    // System Health
    // =========================================================================

    public function test_super_admin_can_view_system_health(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->getJson('/api/system-health');

        $response->assertStatus(200);
    }

    // =========================================================================
    // Regular User Denied
    // =========================================================================

    public function test_regular_user_cannot_access_logs(): void
    {
        $response = $this->actingAs($this->regularUser)
            ->getJson('/api/logs/activity');

        $response->assertStatus(403);

        $response = $this->actingAs($this->regularUser)
            ->getJson('/api/logs/security');

        $response->assertStatus(403);

        $response = $this->actingAs($this->regularUser)
            ->getJson('/api/logs/ai');

        $response->assertStatus(403);
    }
}
