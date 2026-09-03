<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminStudentBulkTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::create([
            'nis' => 'ADMIN001',
            'name' => 'Admin Utama',
            'password' => Hash::make('secret123'),
            'role' => 'admin',
        ]);
    }

    public function test_bulk_store_creates_multiple_students_with_hashed_passwords(): void
    {
        $response = $this->actingAs($this->admin())->postJson('/admin/students/bulk', [
            'kelas' => 'X PPLG',
            'rows' => [
                ['name' => 'Andi', 'nis' => '1001', 'password' => 'password123'],
                ['name' => 'Budi', 'nis' => '1002', 'password' => 'password456'],
                ['name' => 'Citra', 'nis' => '1003', 'password' => 'password789'],
            ],
        ]);

        $response->assertOk()
            ->assertJson(['success' => 3, 'failed' => []]);

        $this->assertDatabaseHas('users', ['nis' => '1001', 'name' => 'Andi', 'role' => 'siswa', 'kelas' => 'X PPLG']);

        $saved = User::where('nis', '1001')->first();
        $this->assertNotSame('password123', $saved->password);
        $this->assertTrue(Hash::check('password123', $saved->password));
    }

    public function test_bulk_store_rejects_duplicate_nis_and_invalid_rows(): void
    {
        User::create([
            'nis' => '1001',
            'name' => 'Siswa Lama',
            'password' => Hash::make('secret123'),
            'role' => 'siswa',
        ]);

        $response = $this->actingAs($this->admin())->postJson('/admin/students/bulk', [
            'kelas' => 'X PPLG',
            'rows' => [
                ['name' => 'Andi', 'nis' => '1001', 'password' => 'password123'],
                ['name' => '', 'nis' => '1002', 'password' => 'password456'],
                ['name' => 'Budi', 'nis' => '1003', 'password' => '123'],
                ['name' => 'Dedi', 'nis' => '1004', 'password' => 'password789'],
            ],
        ]);

        $response->assertOk()
            ->assertJsonPath('success', 1)
            ->assertJsonCount(3, 'failed');

        $this->assertDatabaseMissing('users', ['nis' => '1002']);
        $this->assertDatabaseMissing('users', ['nis' => '1003']);
        $this->assertDatabaseHas('users', ['nis' => '1004']);

        $reasons = collect($response->json('failed'))->pluck('reason')->join(' ');
        $this->assertStringContainsString('sudah terdaftar', $reasons);
        $this->assertStringContainsString('Nama siswa kosong', $reasons);
        $this->assertStringContainsString('minimal 6 karakter', $reasons);
    }

    public function test_bulk_store_rejects_duplicate_nis_within_batch(): void
    {
        $response = $this->actingAs($this->admin())->postJson('/admin/students/bulk', [
            'kelas' => 'XI TJKT',
            'rows' => [
                ['name' => 'Andi', 'nis' => '2001', 'password' => 'password123'],
                ['name' => 'Budi', 'nis' => '2001', 'password' => 'password456'],
            ],
        ]);

        $response->assertOk()->assertJsonPath('success', 1);

        $this->assertEquals(1, User::where('nis', '2001')->count());
        $this->assertStringContainsString('duplikat', $response->json('failed.0.reason'));
    }

    public function test_bulk_store_rejects_invalid_kelas(): void
    {
        $response = $this->actingAs($this->admin())->postJson('/admin/students/bulk', [
            'kelas' => 'X RPL 2',
            'rows' => [
                ['name' => 'Andi', 'nis' => '3001', 'password' => 'password123'],
            ],
        ]);

        $response->assertOk()->assertJsonPath('success', 0);
        $this->assertDatabaseMissing('users', ['nis' => '3001']);
    }

    public function test_import_preview_validates_csv_rows(): void
    {
        $csv = "Nama Siswa,NIS,Password\nAndi,4001,password123\n,4002,password456\nBudi,4003,123\n";

        $response = $this->actingAs($this->admin())->post('/admin/students/import/preview', [
            'file' => UploadedFile::fake()->createWithContent('siswa.csv', $csv),
        ]);

        $response->assertOk()->assertJsonCount(3, 'rows');

        $rows = $response->json('rows');
        $this->assertTrue($rows[0]['valid']);
        $this->assertFalse($rows[1]['valid']);
        $this->assertFalse($rows[2]['valid']);
        $this->assertEquals('Nama siswa kosong', $rows[1]['errors'][0]);
        $this->assertEquals('Password minimal 6 karakter', $rows[2]['errors'][0]);
    }

    public function test_import_preview_rejects_non_readable_file(): void
    {
        $response = $this->actingAs($this->admin())->post('/admin/students/import/preview', [
            'file' => UploadedFile::fake()->create('data.txt', 100),
        ]);

        $response->assertStatus(422)->assertJsonStructure(['error']);
    }

    public function test_import_store_saves_only_valid_rows(): void
    {
        $response = $this->actingAs($this->admin())->postJson('/admin/students/import/store', [
            'kelas' => 'XII ACP',
            'rows' => [
                ['name' => 'Eka', 'nis' => '5001', 'password' => 'password123'],
                ['name' => '', 'nis' => '5002', 'password' => 'password456'],
            ],
        ]);

        $response->assertOk()->assertJsonPath('success', 1);

        $this->assertDatabaseHas('users', ['nis' => '5001', 'kelas' => 'XII ACP']);
        $this->assertDatabaseMissing('users', ['nis' => '5002']);
    }

    public function test_dashboard_renders_management_card_for_registered_view(): void
    {
        $admin = $this->admin();

        $response = $this->actingAs($admin)->get('/admin?view=registered');

        $response->assertOk()
            ->assertSee('Manajemen Siswa')
            ->assertSee('Tambah Siswa Massal')
            ->assertSee('Import Siswa');

        $response = $this->actingAs($admin)->get('/admin');
        $response->assertOk()->assertSee('Rekap Minggu Ini');
    }

    public function test_template_download_returns_csv(): void
    {
        $response = $this->actingAs($this->admin())->get('/admin/students/template');

        $response->assertOk()
            ->assertHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->assertHeader('Content-Disposition', 'attachment; filename="template-siswa.csv"')
            ->assertSee('Nama Siswa,NIS,Password');
    }
}
