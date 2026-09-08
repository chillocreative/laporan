<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
        $this->seed(PermissionSeeder::class);
        $this->seed(SettingsSeeder::class);
    }

    // ─── Helpers ─────────────────────────────────────────────

    protected function createUser(array $overrides = [], ?string $roleSlug = 'user'): User
    {
        $user = User::factory()->create($overrides);

        if ($roleSlug) {
            $role = Role::where('slug', $roleSlug)->firstOrFail();
            $user->roles()->attach($role);
        }

        return $user->refresh();
    }

    protected function makeOrganizationRole(string $name = 'Kementerian Ujian'): Role
    {
        return Role::create([
            'name' => $name,
            'slug' => \Illuminate\Support\Str::slug($name),
            'description' => 'Organisasi ujian.',
            'is_system' => false,
        ]);
    }

    // ─── Login ───────────────────────────────────────────────

    public function test_user_can_login_with_valid_credentials(): void
    {
        $this->createUser(['email' => 'john@example.com']);

        $response = $this->postJson('/api/login', [
            'email' => 'john@example.com',
            'password' => 'password',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'message',
                'user' => ['id', 'name', 'email', 'is_active', 'roles'],
            ])
            ->assertJsonPath('user.email', 'john@example.com')
            ->assertJsonPath('message', 'Log masuk berjaya.');
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        $this->createUser(['email' => 'john@example.com']);

        $response = $this->postJson('/api/login', [
            'email' => 'john@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_login_fails_when_account_is_inactive(): void
    {
        $this->createUser([
            'email' => 'inactive@example.com',
            'is_active' => false,
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'inactive@example.com',
            'password' => 'password',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email'])
            ->assertJsonFragment([
                'email' => ['Akaun anda telah dinyahaktifkan.'],
            ]);
    }

    public function test_login_validation_requires_email_and_password(): void
    {
        $response = $this->postJson('/api/login', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email', 'password']);
    }

    public function test_login_validation_rejects_invalid_email_format(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'not-an-email',
            'password' => 'password',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_login_validation_rejects_short_password(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'john@example.com',
            'password' => 'short',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);
    }

    // ─── Register ────────────────────────────────────────────

    public function test_user_can_register(): void
    {
        $organization = $this->makeOrganizationRole();

        $response = $this->postJson('/api/register', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'organization' => $organization->name,
            'password' => 'secret1234',
            'password_confirmation' => 'secret1234',
        ]);

        $response->assertCreated()
            ->assertJsonStructure(['message', 'pending_approval'])
            ->assertJsonPath('pending_approval', true);

        $this->assertDatabaseHas('users', [
            'email' => 'jane@example.com',
        ]);
    }

    public function test_register_validates_required_fields(): void
    {
        $response = $this->postJson('/api/register', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors([
                'name',
                'email',
                'organization',
                'password',
            ]);
    }

    public function test_register_validates_unique_email(): void
    {
        $organization = $this->makeOrganizationRole();
        $this->createUser(['email' => 'taken@example.com']);

        $response = $this->postJson('/api/register', [
            'name' => 'Duplicate',
            'email' => 'taken@example.com',
            'organization' => $organization->name,
            'password' => 'secret1234',
            'password_confirmation' => 'secret1234',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_register_validates_password_confirmation(): void
    {
        $organization = $this->makeOrganizationRole();

        $response = $this->postJson('/api/register', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'organization' => $organization->name,
            'password' => 'secret1234',
            'password_confirmation' => 'mismatch99',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);
    }

    public function test_register_rejects_system_role_as_organization(): void
    {
        // System roles (admin, super-admin, user) are not valid organisations.
        $response = $this->postJson('/api/register', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'organization' => 'Admin',
            'password' => 'secret1234',
            'password_confirmation' => 'secret1234',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['organization']);
    }

    // ─── Authenticated user profile ──────────────────────────

    public function test_authenticated_user_can_fetch_profile(): void
    {
        $user = $this->createUser(['email' => 'john@example.com']);

        $response = $this->actingAs($user)->getJson('/api/user');

        $response->assertOk()
            ->assertJsonStructure([
                'user' => ['id', 'name', 'email', 'is_active', 'roles', 'permissions'],
            ])
            ->assertJsonPath('user.email', 'john@example.com');
    }

    public function test_unauthenticated_user_cannot_fetch_profile(): void
    {
        $response = $this->getJson('/api/user');

        $response->assertUnauthorized();
    }

    // ─── Logout ──────────────────────────────────────────────

    public function test_user_can_logout(): void
    {
        $user = $this->createUser();

        $this->actingAs($user)
            ->withHeaders(['Origin' => config('app.url')])
            ->postJson('/api/logout')
            ->assertOk()
            ->assertJsonPath('message', 'Berjaya log keluar.');
    }

    public function test_unauthenticated_user_cannot_logout(): void
    {
        $response = $this->postJson('/api/logout');

        $response->assertUnauthorized();
    }
}
