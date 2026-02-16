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

    /**
     * Create a user via the factory with a known plain-text password.
     *
     * The factory already hashes the password with Hash::make and the User
     * model has the `hashed` cast, but Laravel's hashed cast is idempotent
     * (it skips values that are already bcrypt/argon hashes).
     */
    protected function createUser(array $overrides = [], ?string $roleSlug = 'user'): User
    {
        $user = User::factory()->create($overrides);

        if ($roleSlug) {
            $role = Role::where('slug', $roleSlug)->firstOrFail();
            $user->roles()->attach($role);
        }

        return $user->refresh();
    }

    // ─── Login ───────────────────────────────────────────────

    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = $this->createUser(['email' => 'john@example.com']);

        $response = $this->postJson('/api/login', [
            'email' => 'john@example.com',
            'password' => 'password', // factory default
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'message',
                'user' => ['id', 'name', 'email', 'is_active', 'roles'],
            ])
            ->assertJsonPath('user.email', 'john@example.com')
            ->assertJsonPath('message', 'Login successful.');
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
                'email' => ['Your account has been deactivated.'],
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
        $role = Role::where('slug', 'user')->firstOrFail();

        // SettingsSeeder leaves recaptcha_secret_key as null, so
        // RecaptchaService::verify() will return true (skip verification).
        $response = $this->postJson('/api/register', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'secret1234',
            'password_confirmation' => 'secret1234',
            'role_id' => $role->id,
            'recaptcha_token' => 'test-token',
        ]);

        $response->assertCreated()
            ->assertJsonStructure([
                'message',
                'user' => ['id', 'name', 'email'],
            ])
            ->assertJsonPath('user.email', 'jane@example.com')
            ->assertJsonPath('message', 'Registration successful.');

        $this->assertDatabaseHas('users', [
            'email' => 'jane@example.com',
            'name' => 'Jane Doe',
        ]);

        // Verify role was assigned
        $newUser = User::where('email', 'jane@example.com')->first();
        $this->assertTrue($newUser->hasRole('user'));
    }

    public function test_register_validates_required_fields(): void
    {
        $response = $this->postJson('/api/register', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors([
                'name',
                'email',
                'password',
                'role_id',
                'recaptcha_token',
            ]);
    }

    public function test_register_validates_unique_email(): void
    {
        $this->createUser(['email' => 'taken@example.com']);
        $role = Role::where('slug', 'user')->firstOrFail();

        $response = $this->postJson('/api/register', [
            'name' => 'Duplicate',
            'email' => 'taken@example.com',
            'password' => 'secret1234',
            'password_confirmation' => 'secret1234',
            'role_id' => $role->id,
            'recaptcha_token' => 'test-token',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_register_validates_password_confirmation(): void
    {
        $role = Role::where('slug', 'user')->firstOrFail();

        $response = $this->postJson('/api/register', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'secret1234',
            'password_confirmation' => 'mismatch99',
            'role_id' => $role->id,
            'recaptcha_token' => 'test-token',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);
    }

    public function test_register_validates_role_must_exist(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'secret1234',
            'password_confirmation' => 'secret1234',
            'role_id' => 9999,
            'recaptcha_token' => 'test-token',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['role_id']);
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

        // The logout endpoint calls Auth::guard('web')->logout() and
        // invalidates the session.  We add the Origin header so that
        // EnsureFrontendRequestsAreStateful boots the session middleware
        // (needed because AuthService calls request()->session()).
        $this->actingAs($user)
            ->withHeaders(['Origin' => config('app.url')])
            ->postJson('/api/logout')
            ->assertOk()
            ->assertJsonPath('message', 'Logged out successfully.');
    }

    public function test_unauthenticated_user_cannot_logout(): void
    {
        $response = $this->postJson('/api/logout');

        $response->assertUnauthorized();
    }
}
