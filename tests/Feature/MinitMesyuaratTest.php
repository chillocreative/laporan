<?php

namespace Tests\Feature;

use App\Models\MinitMesyuarat;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MinitMesyuaratTest extends TestCase
{
    use RefreshDatabase;

    protected User $mpkk;

    protected User $otherMpkk;

    protected User $user;

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
    }

    public function test_mpkk_user_can_upload_pdf_minit_mesyuarat(): void
    {
        $response = $this->actingAs($this->mpkk)->postJson('/api/minit-mesyuarat', [
            'bulan' => '2026-09-01',
            'file' => UploadedFile::fake()->create('minit.pdf', 500, 'application/pdf'),
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('minit_mesyuarats', [
            'user_id' => $this->mpkk->id,
            'bulan' => '2026-09-01',
            'original_name' => 'minit.pdf',
        ]);
    }

    public function test_mpkk_user_can_upload_docx_minit_mesyuarat(): void
    {
        $response = $this->actingAs($this->mpkk)->postJson('/api/minit-mesyuarat', [
            'bulan' => '2026-09-01',
            'file' => UploadedFile::fake()->create('minit.docx', 500, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'),
        ]);

        $response->assertCreated();
    }

    public function test_image_file_is_rejected(): void
    {
        $response = $this->actingAs($this->mpkk)->postJson('/api/minit-mesyuarat', [
            'bulan' => '2026-09-01',
            'file' => UploadedFile::fake()->create('minit.jpg', 500, 'image/jpeg'),
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors('file');
    }

    public function test_duplicate_month_for_same_user_is_rejected(): void
    {
        MinitMesyuarat::create([
            'user_id' => $this->mpkk->id,
            'bulan' => '2026-09-01',
            'original_name' => 'existing.pdf',
            'file_path' => 'minit-mesyuarat/x/existing.pdf',
            'file_size' => 100,
            'mime_type' => 'application/pdf',
        ]);

        $response = $this->actingAs($this->mpkk)->postJson('/api/minit-mesyuarat', [
            'bulan' => '2026-09-01',
            'file' => UploadedFile::fake()->create('minit.pdf', 500, 'application/pdf'),
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors('bulan');
    }

    public function test_index_only_returns_own_records(): void
    {
        MinitMesyuarat::create([
            'user_id' => $this->mpkk->id,
            'bulan' => '2026-08-01',
            'original_name' => 'mine.pdf',
            'file_path' => 'minit-mesyuarat/mine.pdf',
            'file_size' => 100,
            'mime_type' => 'application/pdf',
        ]);
        MinitMesyuarat::create([
            'user_id' => $this->otherMpkk->id,
            'bulan' => '2026-08-01',
            'original_name' => 'theirs.pdf',
            'file_path' => 'minit-mesyuarat/theirs.pdf',
            'file_size' => 100,
            'mime_type' => 'application/pdf',
        ]);

        $response = $this->actingAs($this->mpkk)->getJson('/api/minit-mesyuarat');

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('mine.pdf', $response->json('data.0.original_name'));
    }

    public function test_non_mpkk_user_is_forbidden(): void
    {
        $response = $this->actingAs($this->user)->getJson('/api/minit-mesyuarat');

        $response->assertForbidden();
    }

    public function test_user_cannot_delete_another_users_record(): void
    {
        $record = MinitMesyuarat::create([
            'user_id' => $this->otherMpkk->id,
            'bulan' => '2026-08-01',
            'original_name' => 'theirs.pdf',
            'file_path' => 'minit-mesyuarat/theirs.pdf',
            'file_size' => 100,
            'mime_type' => 'application/pdf',
        ]);

        $response = $this->actingAs($this->mpkk)->deleteJson("/api/minit-mesyuarat/{$record->id}");

        $response->assertForbidden();
        $this->assertDatabaseHas('minit_mesyuarats', ['id' => $record->id]);
    }

    public function test_mpkk_user_can_replace_file_on_update(): void
    {
        Storage::disk('private')->put('minit-mesyuarat/old.pdf', 'old-content');
        $record = MinitMesyuarat::create([
            'user_id' => $this->mpkk->id,
            'bulan' => '2026-08-01',
            'original_name' => 'old.pdf',
            'file_path' => 'minit-mesyuarat/old.pdf',
            'file_size' => 100,
            'mime_type' => 'application/pdf',
        ]);

        $response = $this->actingAs($this->mpkk)->postJson("/api/minit-mesyuarat/{$record->id}", [
            '_method' => 'PUT',
            'bulan' => '2026-08-01',
            'file' => UploadedFile::fake()->create('new.pdf', 500, 'application/pdf'),
        ]);

        $response->assertOk();
        $record->refresh();
        $this->assertEquals('new.pdf', $record->original_name);
        Storage::disk('private')->assertMissing('minit-mesyuarat/old.pdf');
        Storage::disk('private')->assertExists($record->file_path);
    }
}
