<?php

namespace Tests\Feature;

use App\Models\PenyataKewangan;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PenyataKewanganTest extends TestCase
{
    use RefreshDatabase;

    protected User $mpkk;

    protected User $otherMpkk;

    protected User $user;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('private');

        $this->seed(RoleSeeder::class);

        $this->mpkk = User::factory()->create(['is_active' => true]);
        $this->mpkk->roles()->attach(Role::where('slug', 'mpkk')->first());

        $this->otherMpkk = User::factory()->create(['is_active' => true]);
        $this->otherMpkk->roles()->attach(Role::where('slug', 'mpkk')->first());

        $this->user = User::factory()->create(['is_active' => true]);
        $this->user->roles()->attach(Role::where('slug', 'user')->first());

        $this->admin = User::factory()->create(['is_active' => true]);
        $this->admin->roles()->attach(Role::where('slug', 'admin')->first());
    }

    public function test_mpkk_user_can_upload_penyata_kewangan(): void
    {
        $response = $this->actingAs($this->mpkk)->postJson('/api/penyata-kewangan', [
            'bulan' => '2026-09-01',
            'file' => UploadedFile::fake()->create('penyata.pdf', 500, 'application/pdf'),
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('penyata_kewangans', [
            'user_id' => $this->mpkk->id,
            'bulan' => '2026-09-01',
            'original_name' => 'penyata.pdf',
        ]);
    }

    public function test_non_pdf_file_is_rejected(): void
    {
        $response = $this->actingAs($this->mpkk)->postJson('/api/penyata-kewangan', [
            'bulan' => '2026-09-01',
            'file' => UploadedFile::fake()->create('penyata.docx', 500, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'),
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors('file');
    }

    public function test_duplicate_month_for_same_user_is_rejected(): void
    {
        PenyataKewangan::create([
            'user_id' => $this->mpkk->id,
            'bulan' => '2026-09-01',
            'original_name' => 'existing.pdf',
            'file_path' => 'penyata-kewangan/x/existing.pdf',
            'file_size' => 100,
            'mime_type' => 'application/pdf',
        ]);

        $response = $this->actingAs($this->mpkk)->postJson('/api/penyata-kewangan', [
            'bulan' => '2026-09-01',
            'file' => UploadedFile::fake()->create('penyata.pdf', 500, 'application/pdf'),
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors('bulan');
    }

    public function test_same_month_is_allowed_for_different_users(): void
    {
        PenyataKewangan::create([
            'user_id' => $this->otherMpkk->id,
            'bulan' => '2026-09-01',
            'original_name' => 'existing.pdf',
            'file_path' => 'penyata-kewangan/x/existing.pdf',
            'file_size' => 100,
            'mime_type' => 'application/pdf',
        ]);

        $response = $this->actingAs($this->mpkk)->postJson('/api/penyata-kewangan', [
            'bulan' => '2026-09-01',
            'file' => UploadedFile::fake()->create('penyata.pdf', 500, 'application/pdf'),
        ]);

        $response->assertCreated();
    }

    public function test_index_only_returns_own_records(): void
    {
        PenyataKewangan::create([
            'user_id' => $this->mpkk->id,
            'bulan' => '2026-08-01',
            'original_name' => 'mine.pdf',
            'file_path' => 'penyata-kewangan/mine.pdf',
            'file_size' => 100,
            'mime_type' => 'application/pdf',
        ]);
        PenyataKewangan::create([
            'user_id' => $this->otherMpkk->id,
            'bulan' => '2026-08-01',
            'original_name' => 'theirs.pdf',
            'file_path' => 'penyata-kewangan/theirs.pdf',
            'file_size' => 100,
            'mime_type' => 'application/pdf',
        ]);

        $response = $this->actingAs($this->mpkk)->getJson('/api/penyata-kewangan');

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('mine.pdf', $response->json('data.0.original_name'));
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}$/', $response->json('data.0.bulan'));
    }

    public function test_non_mpkk_user_is_forbidden(): void
    {
        $response = $this->actingAs($this->user)->getJson('/api/penyata-kewangan');

        $response->assertForbidden();
    }

    public function test_user_cannot_update_another_users_record(): void
    {
        $record = PenyataKewangan::create([
            'user_id' => $this->otherMpkk->id,
            'bulan' => '2026-08-01',
            'original_name' => 'theirs.pdf',
            'file_path' => 'penyata-kewangan/theirs.pdf',
            'file_size' => 100,
            'mime_type' => 'application/pdf',
        ]);

        $response = $this->actingAs($this->mpkk)->postJson("/api/penyata-kewangan/{$record->id}", [
            '_method' => 'PUT',
            'bulan' => '2026-08-01',
        ]);

        $response->assertForbidden();
    }

    public function test_mpkk_user_can_update_own_record_month_without_replacing_file(): void
    {
        $record = PenyataKewangan::create([
            'user_id' => $this->mpkk->id,
            'bulan' => '2026-08-01',
            'original_name' => 'mine.pdf',
            'file_path' => 'penyata-kewangan/mine.pdf',
            'file_size' => 100,
            'mime_type' => 'application/pdf',
        ]);

        $response = $this->actingAs($this->mpkk)->postJson("/api/penyata-kewangan/{$record->id}", [
            '_method' => 'PUT',
            'bulan' => '2026-10-01',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('penyata_kewangans', [
            'id' => $record->id,
            'bulan' => '2026-10-01',
            'file_path' => 'penyata-kewangan/mine.pdf',
        ]);
    }

    public function test_mpkk_user_can_delete_own_record(): void
    {
        Storage::disk('private')->put('penyata-kewangan/mine.pdf', 'dummy');
        $record = PenyataKewangan::create([
            'user_id' => $this->mpkk->id,
            'bulan' => '2026-08-01',
            'original_name' => 'mine.pdf',
            'file_path' => 'penyata-kewangan/mine.pdf',
            'file_size' => 100,
            'mime_type' => 'application/pdf',
        ]);

        $response = $this->actingAs($this->mpkk)->deleteJson("/api/penyata-kewangan/{$record->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('penyata_kewangans', ['id' => $record->id]);
        Storage::disk('private')->assertMissing('penyata-kewangan/mine.pdf');
    }

    // ------------------------------------------------------------------
    // Admin oversight
    // ------------------------------------------------------------------

    public function test_admin_sees_records_from_all_mpkk_users(): void
    {
        PenyataKewangan::create([
            'user_id' => $this->mpkk->id,
            'bulan' => '2026-08-01',
            'original_name' => 'mine.pdf',
            'file_path' => 'penyata-kewangan/mine.pdf',
            'file_size' => 100,
            'mime_type' => 'application/pdf',
        ]);
        PenyataKewangan::create([
            'user_id' => $this->otherMpkk->id,
            'bulan' => '2026-08-01',
            'original_name' => 'theirs.pdf',
            'file_path' => 'penyata-kewangan/theirs.pdf',
            'file_size' => 100,
            'mime_type' => 'application/pdf',
        ]);

        $response = $this->actingAs($this->admin)->getJson('/api/penyata-kewangan');

        $response->assertOk();
        $this->assertCount(2, $response->json('data'));
        $this->assertNotNull($response->json('data.0.user'));
    }

    public function test_admin_cannot_create_records(): void
    {
        $response = $this->actingAs($this->admin)->postJson('/api/penyata-kewangan', [
            'bulan' => '2026-09-01',
            'file' => UploadedFile::fake()->create('penyata.pdf', 500, 'application/pdf'),
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('penyata_kewangans', ['bulan' => '2026-09-01']);
    }

    public function test_admin_can_update_and_delete_any_mpkk_users_record(): void
    {
        $record = PenyataKewangan::create([
            'user_id' => $this->mpkk->id,
            'bulan' => '2026-08-01',
            'original_name' => 'mine.pdf',
            'file_path' => 'penyata-kewangan/mine.pdf',
            'file_size' => 100,
            'mime_type' => 'application/pdf',
        ]);

        $update = $this->actingAs($this->admin)->postJson("/api/penyata-kewangan/{$record->id}", [
            '_method' => 'PUT',
            'bulan' => '2026-10-01',
        ]);
        $update->assertOk();
        $this->assertDatabaseHas('penyata_kewangans', ['id' => $record->id, 'bulan' => '2026-10-01']);

        $delete = $this->actingAs($this->admin)->deleteJson("/api/penyata-kewangan/{$record->id}");
        $delete->assertOk();
        $this->assertDatabaseMissing('penyata_kewangans', ['id' => $record->id]);
    }
}
