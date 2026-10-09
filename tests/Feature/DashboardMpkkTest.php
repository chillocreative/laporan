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
            ->assertJsonPath('data.mpkk.total_minit_mesyuarat', 0)
            ->assertJsonPath('data.mpkk.users.0.user_name', 'MPKK TEST')
            ->assertJsonPath('data.mpkk.users.0.report_count', 0)
            ->assertJsonPath('data.mpkk.users.0.penyata_kewangan_count', 0)
            ->assertJsonPath('data.mpkk.users.0.minit_mesyuarat_count', 0);
    }

    public function test_dashboard_groups_mpkk_by_dun(): void
    {
        $this->seed(RoleSeeder::class);

        $admin = User::factory()->create(['is_active' => true]);
        $admin->roles()->attach(Role::where('slug', 'admin')->first());

        foreach (['MPKK JALAN KEDAH', 'MPKK BUMBUNG LIMA', 'MPKK KUALA MUDA', 'MPKK ENTAH'] as $name) {
            $u = User::factory()->create(['is_active' => true, 'name' => $name]);
            $u->roles()->attach(Role::where('slug', 'mpkk')->first());
        }

        $duns = collect($this->actingAs($admin)->getJson('/api/dashboard')->assertOk()->json('data.mpkk.duns'))
            ->keyBy('dun');

        $this->assertSame(['Pinang Tunggal', 'Bertam', 'Penaga', 'Belum Ditetapkan'], $duns->keys()->all());
        $this->assertSame('MPKK BUMBUNG LIMA', $duns['Pinang Tunggal']['users'][0]['user_name']);
        $this->assertSame(1, $duns['Bertam']['total_mpkk']);
        $this->assertSame('MPKK ENTAH', $duns['Belum Ditetapkan']['users'][0]['user_name']);
    }
}
