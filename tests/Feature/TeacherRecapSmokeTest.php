<?php

namespace Tests\Feature;

use App\Models\Journal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TeacherRecapSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_recap_page_renders_and_detail_api_works(): void
    {
        $guru = User::create([
            'nis' => 'GURU001', 'name' => 'Guru Wali', 'password' => Hash::make('secret123'), 'role' => 'guru',
        ]);

        $siswa = User::create([
            'nis' => 'SIS001', 'name' => 'Siswa Test', 'password' => Hash::make('secret123'),
            'role' => 'siswa', 'kelas' => 'X PPLG',
        ]);

        $yesterday = now()->subDay()->toDateString();
        Journal::create(['user_id' => $siswa->id, 'date' => $yesterday, 'bangun_pagi' => true, 'bangun_pagi_time' => '05:00', 'beribadah' => true, 'berolahraga' => true, 'makan_sehat' => true, 'gemar_belajar' => true, 'bermasyarakat' => true, 'tidur_cepat' => true, 'tidur_note' => '21:00', 'completed_count' => 7, 'is_fully_completed' => true]);

        $response = $this->actingAs($guru)->get('/teacher');
        $response->assertOk();
        $this->assertStringContainsString('Rekap Kedisiplinan Siswa', $response->getContent());
        $this->assertStringContainsString('Siswa Test', $response->getContent());

        $detail = $this->actingAs($guru)->getJson('/api/teacher/student/'.$siswa->id);
        $detail->assertOk();
        $this->assertSame('Siswa Test', $detail->json('student.name'));
        $this->assertSame(1, $detail->json('streak'));
        $this->assertCount(1, $detail->json('journals'));
    }

    public function test_pwa_links_present_on_pages(): void
    {
        $guest = $this->get('/login');
        $guest->assertOk()
            ->assertSee('/manifest.json', false)
            ->assertSee('/sw.js', false)
            ->assertSee('/icons/icon-192.png', false)
            ->assertSee('apple-touch-icon', false);

        $admin = User::create([
            'nis' => 'ADMINPWA', 'name' => 'Admin PWA', 'password' => Hash::make('secret123'), 'role' => 'admin',
        ]);
        $app = $this->actingAs($admin)->get('/admin?view=registered');
        $app->assertOk()
            ->assertSee('/manifest.json', false)
            ->assertSee('/sw.js', false)
            ->assertSee('data-pwa-install', false);
    }
}
