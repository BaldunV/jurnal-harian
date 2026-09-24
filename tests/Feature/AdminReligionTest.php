<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\JournalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AdminReligionTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('religionProvider')]
    public function test_admin_creates_student_with_derived_worship_type(string $religion, string $worshipType): void
    {
        $response = $this->actingAs($this->admin())->postJson('/admin/students/bulk', [
            'kelas' => 'X PPLG',
            'rows' => [[
                'name' => 'Siswa '.ucfirst($religion),
                'nis' => 'CREATE-'.strtoupper($religion),
                'password' => 'password123',
                'religion' => $religion,
                'worship_type' => $worshipType === 'muslim' ? 'non_muslim' : 'muslim',
            ]],
        ]);

        $response->assertOk()->assertJsonPath('success', 1);
        $this->assertDatabaseHas('users', [
            'nis' => 'CREATE-'.strtoupper($religion),
            'religion' => $religion,
            'worship_type' => $worshipType,
            'role' => 'siswa',
        ]);
    }

    public function test_invalid_religion_is_rejected(): void
    {
        $student = $this->student();

        $response = $this->actingAs($this->admin())->put("/admin/students/{$student->id}", [
            'religion' => 'invalid',
        ]);

        $response->assertSessionHasErrors('religion');
        $this->assertSame('islam', $student->fresh()->religion);
    }

    public function test_islam_changed_to_hindu_updates_only_religion_and_worship_type(): void
    {
        $student = $this->student(['religion' => 'islam']);
        $original = $student->only(['nis', 'name', 'kelas', 'role', 'password']);

        $this->actingAs($this->admin())->put("/admin/students/{$student->id}", [
            'religion' => 'hindu',
        ])->assertRedirect();

        $student->refresh();
        $this->assertSame('hindu', $student->religion);
        $this->assertSame('non_muslim', $student->worship_type);
        $this->assertSame($original, $student->only(array_keys($original)));
    }

    public function test_hindu_changed_to_islam_updates_worship_type_to_muslim(): void
    {
        $student = $this->student(['religion' => 'hindu']);

        $this->actingAs($this->admin())->put("/admin/students/{$student->id}", [
            'religion' => 'islam',
        ])->assertRedirect();

        $student->refresh();
        $this->assertSame('islam', $student->religion);
        $this->assertSame('muslim', $student->worship_type);
        $this->assertSame('siswa', $student->role);
    }

    public function test_model_prevents_inconsistent_religion_and_worship_type(): void
    {
        $student = $this->student();

        $student->update([
            'religion' => 'buddha',
            'worship_type' => 'muslim',
        ]);

        $this->assertSame('non_muslim', $student->fresh()->worship_type);
    }

    public function test_admin_cannot_update_religion_of_non_student(): void
    {
        $admin = $this->admin();
        $teacher = User::create([
            'nis' => 'GURU-RELIGION',
            'name' => 'Guru',
            'password' => Hash::make('password123'),
            'role' => 'guru',
            'kelas' => 'X PPLG',
        ]);

        $this->actingAs($admin)->put("/admin/students/{$teacher->id}", [
            'religion' => 'hindu',
        ])->assertNotFound();
        $this->actingAs($admin)->put("/admin/students/{$admin->id}", [
            'religion' => 'islam',
        ])->assertNotFound();

        $this->assertNull($teacher->fresh()->religion);
        $this->assertNull($admin->fresh()->religion);
    }

    public function test_student_cannot_change_worship_type_from_web_profile(): void
    {
        $student = $this->student(['religion' => 'islam']);

        $this->actingAs($student)->post('/profile', [
            'worship_type' => 'non_muslim',
        ])->assertForbidden();

        $this->assertSame('muslim', $student->fresh()->worship_type);
    }

    #[DataProvider('religionProvider')]
    public function test_dashboard_filters_each_religion(string $religion): void
    {
        $targetName = 'Target '.ucfirst($religion);
        $this->student([
            'name' => $targetName,
            'nis' => 'FILTER-'.strtoupper($religion),
            'religion' => $religion,
        ]);
        $this->student([
            'name' => 'Decoy '.$religion,
            'nis' => 'DECOY-'.strtoupper($religion),
            'religion' => $religion === 'islam' ? 'hindu' : 'islam',
        ]);

        $response = $this->actingAs($this->admin())
            ->get('/admin?view=registered&religion='.$religion);

        $response->assertOk()
            ->assertSee($targetName)
            ->assertDontSee('Decoy '.$religion);
    }

    public function test_contradictory_worship_and_religion_filters_return_empty_results(): void
    {
        $this->student(['name' => 'Siswa Hindu', 'religion' => 'hindu']);

        $this->actingAs($this->admin())
            ->get('/admin?view=registered&worship_type=muslim&religion=hindu')
            ->assertOk()
            ->assertDontSee('Siswa Hindu')
            ->assertSee('Belum ada siswa yang sesuai dengan filter.');
    }

    public function test_existing_worship_type_filters_still_work(): void
    {
        $this->student(['name' => 'Siswa Islam Filter', 'religion' => 'islam']);
        $this->student(['name' => 'Siswa Hindu Filter', 'religion' => 'hindu']);
        $admin = $this->admin();

        $this->actingAs($admin)
            ->get('/admin?view=registered&worship_type=muslim')
            ->assertOk()
            ->assertSee('Siswa Islam Filter')
            ->assertDontSee('Siswa Hindu Filter');

        $this->actingAs($admin)
            ->get('/admin?view=registered&worship_type=non_muslim')
            ->assertOk()
            ->assertSee('Siswa Hindu Filter')
            ->assertDontSee('Siswa Islam Filter');
    }

    public function test_search_works_with_religion_filter(): void
    {
        $this->student(['name' => 'Made Khusus', 'nis' => 'SEARCH-001', 'religion' => 'hindu']);
        $this->student(['name' => 'Made Lain', 'nis' => 'SEARCH-002', 'religion' => 'islam']);

        $this->actingAs($this->admin())
            ->get('/admin?view=registered&religion=hindu&search=SEARCH-001')
            ->assertOk()
            ->assertSee('Made Khusus')
            ->assertDontSee('Made Lain');
    }

    public function test_pagination_preserves_religion_filter(): void
    {
        for ($index = 1; $index <= 21; $index++) {
            $this->student([
                'name' => 'Hindu '.$index,
                'nis' => 'PAGE-'.str_pad((string) $index, 3, '0', STR_PAD_LEFT),
                'religion' => 'hindu',
            ]);
        }

        $this->actingAs($this->admin())
            ->get('/admin?view=registered&religion=hindu')
            ->assertOk()
            ->assertSee('religion=hindu', false)
            ->assertSee('page=2', false);
    }

    public function test_existing_non_muslim_without_religion_is_not_guessed(): void
    {
        $student = $this->student([
            'religion' => null,
            'worship_type' => 'non_muslim',
        ]);

        $this->assertNull($student->religion);
        $this->actingAs($this->admin())
            ->get('/admin?view=registered')
            ->assertOk()
            ->assertSee('Belum ditentukan');
    }

    #[DataProvider('religionProvider')]
    public function test_journal_service_uses_existing_muslim_or_non_muslim_flow(string $religion, string $worshipType): void
    {
        $student = $this->student(['religion' => $religion]);
        $service = new JournalService;
        $method = new \ReflectionMethod($service, 'worshipDetails');
        $method->setAccessible(true);

        $muslimKeys = ['subuh', 'dzuhur', 'ashar', 'maghrib', 'isya'];
        $nonMuslimKeys = ['prayer', 'scripture', 'worship', 'spiritual_activity', 'other'];
        $details = array_fill_keys(array_merge($muslimKeys, $nonMuslimKeys), true);
        $result = $method->invoke($service, $student, $details);

        $this->assertSame($worshipType, $student->worship_type);
        $this->assertSame($worshipType === 'muslim' ? $muslimKeys : $nonMuslimKeys, array_keys($result));
    }

    public static function religionProvider(): array
    {
        return [
            'Islam' => ['islam', 'muslim'],
            'Kristen Protestan' => ['kristen', 'non_muslim'],
            'Katolik' => ['katolik', 'non_muslim'],
            'Hindu' => ['hindu', 'non_muslim'],
            'Buddha' => ['buddha', 'non_muslim'],
            'Konghucu' => ['konghucu', 'non_muslim'],
        ];
    }

    private function admin(): User
    {
        return User::create([
            'nis' => 'ADMIN-RELIGION',
            'name' => 'Admin Utama',
            'password' => Hash::make('secret123'),
            'role' => 'admin',
        ]);
    }

    private function student(array $overrides = []): User
    {
        static $counter = 0;
        $counter++;

        return User::create(array_merge([
            'nis' => 'REL-'.str_pad((string) $counter, 5, '0', STR_PAD_LEFT),
            'name' => 'Siswa '.$counter,
            'password' => Hash::make('password123'),
            'role' => 'siswa',
            'kelas' => 'X PPLG',
            'religion' => 'islam',
            'worship_type' => 'muslim',
        ], $overrides));
    }
}
