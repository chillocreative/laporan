<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardMpkkTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_lists_every_mpkk_user_with_zero_default(): void
    {
        $this->seed(RoleSeeder::class);

        $admin = User::factory()->create(['is_active' => true]);
        $admin->roles()->attach(Role::where('slug', 'admin')->first());

        $mpkk = User::factory()->create(['is_active' => true, 'name' => 'MPKK TEST']);
        $mpkk->roles()->attach(Role::where('slug', 'mpkk')->first());

        $response = $this->actingAs($admin)->getJson('/api/dashboard')->assertOk();

        $response->assertJsonPath('data.mpkk.total_reports', 0)
            ->assertJsonPath('data.mpkk.total_penyata_kewangan', 0)
            ->assertJsonPath('data.mpkk.users.0.user_name', 'MPKK TEST')
            ->assertJsonPath('data.mpkk.users.0.report_count', 0);
    }
}
