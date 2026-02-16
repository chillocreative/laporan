<?php

namespace Tests\Feature;

use App\Enums\ReportStatus;
use App\Models\Report;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $admin;
    protected User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RoleSeeder::class, PermissionSeeder::class]);

        $this->user = User::factory()->create(['is_active' => true]);
        $this->user->roles()->attach(Role::where('slug', 'user')->first());

        $this->admin = User::factory()->create(['is_active' => true]);
        $this->admin->roles()->attach(Role::where('slug', 'admin')->first());

        $this->superAdmin = User::factory()->create(['is_active' => true]);
        $this->superAdmin->roles()->attach(Role::where('slug', 'super-admin')->first());
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    protected function validReportData(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Pothole on Jalan Ampang',
            'category' => 'Infrastructure',
            'location' => 'Jalan Ampang, Kuala Lumpur',
            'description' => 'There is a large pothole causing traffic hazards near the intersection.',
            'incident_date' => now()->subDay()->format('Y-m-d'),
        ], $overrides);
    }

    protected function createReport(array $overrides = []): Report
    {
        return Report::create(array_merge([
            'user_id' => $this->user->id,
            'title' => 'Test Report',
            'category' => 'Infrastructure',
            'location' => 'Test Location',
            'description' => 'Test description for the report.',
            'incident_date' => now()->subDay(),
            'status' => ReportStatus::Pending,
        ], $overrides));
    }

    // ------------------------------------------------------------------
    // 1. Create
    // ------------------------------------------------------------------

    public function test_user_can_create_report(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/reports', $this->validReportData());

        $response->assertStatus(201)
            ->assertJsonPath('message', 'Report created successfully.')
            ->assertJsonPath('data.title', 'Pothole on Jalan Ampang')
            ->assertJsonPath('data.category', 'Infrastructure')
            ->assertJsonPath('data.status.value', 'pending');

        $this->assertDatabaseHas('reports', [
            'user_id' => $this->user->id,
            'title' => 'Pothole on Jalan Ampang',
        ]);
    }

    // ------------------------------------------------------------------
    // 2. Validation
    // ------------------------------------------------------------------

    public function test_report_creation_validates_required_fields(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/reports', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'title',
                'category',
                'location',
                'description',
                'incident_date',
            ]);
    }

    // ------------------------------------------------------------------
    // 3. View own report
    // ------------------------------------------------------------------

    public function test_user_can_view_own_report(): void
    {
        $report = $this->createReport();

        $response = $this->actingAs($this->user)
            ->getJson("/api/reports/{$report->id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $report->id)
            ->assertJsonPath('data.title', 'Test Report');
    }

    // ------------------------------------------------------------------
    // 4. Cannot view other's report
    // ------------------------------------------------------------------

    public function test_user_cannot_view_others_report(): void
    {
        $otherUser = User::factory()->create(['is_active' => true]);
        $otherUser->roles()->attach(Role::where('slug', 'user')->first());

        $report = $this->createReport(['user_id' => $otherUser->id]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/reports/{$report->id}");

        $response->assertForbidden();
    }

    // ------------------------------------------------------------------
    // 5. Admin can view any report
    // ------------------------------------------------------------------

    public function test_admin_can_view_any_report(): void
    {
        $report = $this->createReport(); // owned by $this->user

        $response = $this->actingAs($this->admin)
            ->getJson("/api/reports/{$report->id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $report->id);
    }

    // ------------------------------------------------------------------
    // 6. User lists only own reports
    // ------------------------------------------------------------------

    public function test_user_can_list_own_reports(): void
    {
        // Create 2 reports for $this->user
        $this->createReport(['title' => 'User Report 1']);
        $this->createReport(['title' => 'User Report 2']);

        // Create 1 report for another user
        $otherUser = User::factory()->create(['is_active' => true]);
        $otherUser->roles()->attach(Role::where('slug', 'user')->first());
        $this->createReport(['user_id' => $otherUser->id, 'title' => 'Other Report']);

        $response = $this->actingAs($this->user)
            ->getJson('/api/reports');

        $response->assertOk();

        $data = $response->json('data');
        $this->assertCount(2, $data);

        $titles = collect($data)->pluck('title')->all();
        $this->assertContains('User Report 1', $titles);
        $this->assertContains('User Report 2', $titles);
        $this->assertNotContains('Other Report', $titles);
    }

    // ------------------------------------------------------------------
    // 7. Admin lists all reports
    // ------------------------------------------------------------------

    public function test_admin_can_list_all_reports(): void
    {
        $this->createReport(['title' => 'User Report']);

        $otherUser = User::factory()->create(['is_active' => true]);
        $otherUser->roles()->attach(Role::where('slug', 'user')->first());
        $this->createReport(['user_id' => $otherUser->id, 'title' => 'Other Report']);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/reports');

        $response->assertOk();

        $data = $response->json('data');
        $this->assertCount(2, $data);
    }

    // ------------------------------------------------------------------
    // 8. Update own report
    // ------------------------------------------------------------------

    public function test_user_can_update_own_report(): void
    {
        $report = $this->createReport();

        $response = $this->actingAs($this->user)
            ->putJson("/api/reports/{$report->id}", [
                'title' => 'Updated Title',
            ]);

        $response->assertOk()
            ->assertJsonPath('message', 'Report updated successfully.')
            ->assertJsonPath('data.title', 'Updated Title');

        $this->assertDatabaseHas('reports', [
            'id' => $report->id,
            'title' => 'Updated Title',
        ]);
    }

    // ------------------------------------------------------------------
    // 9. Cannot update other's report
    // ------------------------------------------------------------------

    public function test_user_cannot_update_others_report(): void
    {
        $otherUser = User::factory()->create(['is_active' => true]);
        $otherUser->roles()->attach(Role::where('slug', 'user')->first());

        $report = $this->createReport(['user_id' => $otherUser->id]);

        $response = $this->actingAs($this->user)
            ->putJson("/api/reports/{$report->id}", [
                'title' => 'Hacked Title',
            ]);

        $response->assertForbidden();
    }

    // ------------------------------------------------------------------
    // 10. Delete own report
    // ------------------------------------------------------------------

    public function test_user_can_delete_own_report(): void
    {
        $report = $this->createReport();

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/reports/{$report->id}");

        $response->assertOk()
            ->assertJsonPath('message', 'Report deleted successfully.');

        $this->assertSoftDeleted('reports', ['id' => $report->id]);
    }

    // ------------------------------------------------------------------
    // 11. Admin can update report status
    // ------------------------------------------------------------------

    public function test_admin_can_update_report_status(): void
    {
        $report = $this->createReport();

        $response = $this->actingAs($this->admin)
            ->patchJson("/api/reports/{$report->id}/status", [
                'status' => 'under_review',
            ]);

        $response->assertOk()
            ->assertJsonPath('message', 'Report status updated.')
            ->assertJsonPath('data.status.value', 'under_review');

        $this->assertDatabaseHas('reports', [
            'id' => $report->id,
            'status' => 'under_review',
        ]);
    }

    // ------------------------------------------------------------------
    // 12. Regular user cannot update report status
    // ------------------------------------------------------------------

    public function test_user_cannot_update_report_status(): void
    {
        $report = $this->createReport();

        $response = $this->actingAs($this->user)
            ->patchJson("/api/reports/{$report->id}/status", [
                'status' => 'resolved',
            ]);

        $response->assertForbidden();
    }

    // ------------------------------------------------------------------
    // 13. Admin can trigger AI analysis (OpenAI disabled scenario)
    // ------------------------------------------------------------------

    public function test_admin_can_trigger_ai_analysis(): void
    {
        // OpenAI is disabled by default (no settings seeded, or openai_enabled = 0)
        // The controller returns 422 when AI is disabled.
        $report = $this->createReport();

        $response = $this->actingAs($this->admin)
            ->postJson("/api/reports/{$report->id}/analyze");

        $response->assertStatus(422)
            ->assertJsonPath('message', 'AI analysis is currently disabled. Enable it in Settings.');
    }

    // ------------------------------------------------------------------
    // 14. Analysis status returns not analyzed
    // ------------------------------------------------------------------

    public function test_analysis_status_returns_not_analyzed(): void
    {
        $report = $this->createReport();

        $response = $this->actingAs($this->user)
            ->getJson("/api/reports/{$report->id}/analysis-status");

        $response->assertOk()
            ->assertJsonPath('data.analyzed', false)
            ->assertJsonPath('data.analyzed_at', null)
            ->assertJsonPath('data.risk_level', null);
    }

    // ------------------------------------------------------------------
    // 15. Unauthenticated access denied
    // ------------------------------------------------------------------

    public function test_unauthenticated_cannot_access_reports(): void
    {
        $report = $this->createReport();

        $this->getJson('/api/reports')->assertUnauthorized();
        $this->postJson('/api/reports', $this->validReportData())->assertUnauthorized();
        $this->getJson("/api/reports/{$report->id}")->assertUnauthorized();
        $this->putJson("/api/reports/{$report->id}", ['title' => 'X'])->assertUnauthorized();
        $this->deleteJson("/api/reports/{$report->id}")->assertUnauthorized();
        $this->patchJson("/api/reports/{$report->id}/status", ['status' => 'resolved'])->assertUnauthorized();
        $this->postJson("/api/reports/{$report->id}/analyze")->assertUnauthorized();
        $this->getJson("/api/reports/{$report->id}/analysis-status")->assertUnauthorized();
    }
}
