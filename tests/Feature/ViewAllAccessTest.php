<?php

namespace Tests\Feature;

use App\Models\PenyataKewangan;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ViewAllAccessTest extends TestCase
{
    use RefreshDatabase;

    protected User $viewer;

    protected User $mpkk;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);

        $this->viewer = User::factory()->create(['is_active' => true, 'can_view_all' => true]);
        $this->viewer->roles()->attach(Role::where('slug', 'user')->first());

        $this->mpkk = User::factory()->create(['is_active' => true]);
        $this->mpkk->roles()->attach(Role::where('slug', 'mpkk')->first());
    }

    public function test_viewer_gets_admin_dashboard(): void
    {
        $this->actingAs($this->viewer)->getJson('/api/dashboard')
            ->assertOk()
            ->assertJsonStructure(['data' => ['reports', 'total_users', 'mpkk' => ['users']]]);
    }

    public function test_viewer_can_list_users_categories_and_reports(): void
    {
        $this->actingAs($this->viewer)->getJson('/api/users')->assertOk();
        $this->actingAs($this->viewer)->getJson('/api/categories')->assertOk();
        $this->actingAs($this->viewer)->getJson('/api/reports')->assertOk();
    }

    public function test_viewer_sees_all_mpkk_records_and_can_download(): void
    {
        $record = PenyataKewangan::create([
            'user_id' => $this->mpkk->id,
            'bulan' => '2026-09-01',
            'original_name' => 'a.pdf',
            'file_path' => 'penyata-kewangan/1/a.pdf',
            'file_size' => 1,
            'mime_type' => 'application/pdf',
        ]);

        $this->actingAs($this->viewer)->getJson('/api/penyata-kewangan')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $record->id);
        $this->actingAs($this->viewer)->getJson('/api/minit-mesyuarat')->assertOk();
    }

    public function test_viewer_cannot_modify(): void
    {
        $this->actingAs($this->viewer)->postJson('/api/users', [])->assertForbidden();
        $this->actingAs($this->viewer)->deleteJson("/api/users/{$this->mpkk->id}")->assertForbidden();
        $this->actingAs($this->viewer)->postJson('/api/categories', ['name' => 'X'])->assertForbidden();
        $this->actingAs($this->viewer)->postJson('/api/penyata-kewangan', [])->assertForbidden();
        $this->actingAs($this->viewer)->postJson('/api/minit-mesyuarat', [])->assertForbidden();
    }

    public function test_viewer_cannot_access_system_routes(): void
    {
        $this->actingAs($this->viewer)->getJson('/api/settings')->assertForbidden();
        $this->actingAs($this->viewer)->getJson('/api/logs/activity')->assertForbidden();
    }

    public function test_unflagged_user_is_still_blocked(): void
    {
        $plain = User::factory()->create(['is_active' => true]);
        $plain->roles()->attach(Role::where('slug', 'user')->first());

        $this->actingAs($plain)->getJson('/api/users')->assertForbidden();
        $this->actingAs($plain)->getJson('/api/penyata-kewangan')->assertForbidden();
    }
}
