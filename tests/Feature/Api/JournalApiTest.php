<?php

namespace Tests\Feature\Api;

use App\Models\Journal;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class JournalApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_jakarta_date_and_derived_fields_are_owned_by_the_server(): void
    {
        $this->travelTo(Carbon::parse('2026-08-27 18:30:00', 'UTC'));
        $student = $this->student();
        Sanctum::actingAs($student, ['student']);

        $this->postJson('/api/me/journal', array_merge($this->payload($student), [
            'date' => '2030-01-01',
            'user_id' => 999,
            'beribadah' => false,
            'completed_count' => 99,
            'is_submitted' => true,
        ]))->assertUnprocessable()
            ->assertJsonPath('code', 'validation_error');
        $this->assertDatabaseCount('journals', 0);

        $this->postJson('/api/me/journal', $this->payload($student))
            ->assertCreated()
            ->assertJsonPath('data.date', '2026-08-28')
            ->assertJsonPath('data.beribadah', true)
            ->assertJsonPath('data.completed_count', 7)
            ->assertJsonPath('data.is_fully_completed', true)
            ->assertJsonPath('data.is_submitted', false)
            ->assertJsonMissingPath('data.user_id')
            ->assertJsonMissingPath('data.olahraga_photo')
            ->assertJsonMissingPath('data.makan_photo');

        $this->assertDatabaseHas('journals', [
            'user_id' => $student->id,
            'date' => '2026-08-28',
            'completed_count' => 7,
            'is_submitted' => false,
        ]);

        $this->getJson('/api/me/journal/today')
            ->assertOk()
            ->assertJsonPath('data.date', '2026-08-28')
            ->assertJsonPath('data.journal.completed_count', 7);
    }

    public function test_duplicate_create_update_submit_and_lock_rules_are_enforced(): void
    {
        $student = $this->student();
        Sanctum::actingAs($student, ['student']);
        $payload = $this->payload($student);

        $this->postJson('/api/me/journal', $payload)->assertCreated();
        $this->postJson('/api/me/journal', $payload)
            ->assertConflict()
            ->assertJsonPath('code', 'journal_exists');

        $payload['berolahraga'] = false;
        $this->putJson('/api/me/journal', $payload)
            ->assertOk()
            ->assertJsonPath('data.completed_count', 6);

        $payload['berolahraga'] = true;
        $this->postJson('/api/me/journal/submit', $payload)
            ->assertOk()
            ->assertJsonPath('data.is_submitted', true)
            ->assertJsonPath('data.completed_count', 7);

        $this->putJson('/api/me/journal', $payload)
            ->assertConflict()
            ->assertJsonPath('code', 'journal_locked');
        $this->postJson('/api/me/journal/submit', $payload)
            ->assertConflict()
            ->assertJsonPath('code', 'journal_locked');
    }

    public function test_update_and_submit_require_an_existing_today_draft(): void
    {
        $student = $this->student();
        Sanctum::actingAs($student, ['student']);

        $this->putJson('/api/me/journal', $this->payload($student))
            ->assertNotFound()
            ->assertJsonPath('code', 'journal_not_found');
        $this->postJson('/api/me/journal/submit', $this->payload($student))
            ->assertNotFound()
            ->assertJsonPath('code', 'journal_not_found');
    }

    public function test_journal_detail_and_history_are_scoped_to_the_authenticated_student(): void
    {
        $student = $this->student(['nis' => 'OWNER-001']);
        $other = $this->student(['nis' => 'OWNER-002']);
        $ownJournal = Journal::create([
            'user_id' => $student->id,
            'date' => now('Asia/Jakarta')->subDay()->toDateString(),
        ]);
        $otherJournal = Journal::create([
            'user_id' => $other->id,
            'date' => now('Asia/Jakarta')->subDay()->toDateString(),
        ]);
        Sanctum::actingAs($student, ['student']);

        $this->getJson('/api/me/journals/'.$ownJournal->id)
            ->assertOk()
            ->assertJsonPath('data.id', $ownJournal->id);
        $this->getJson('/api/me/journals/'.$otherJournal->id)
            ->assertNotFound()
            ->assertJsonPath('code', 'not_found');

        $this->getJson('/api/me/journals?per_page=50')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $ownJournal->id)
            ->assertJsonPath('meta.total', 1);
    }

    public function test_worship_details_must_match_the_students_worship_type(): void
    {
        $student = $this->student([
            'nis' => 'NON-MUSLIM-001',
            'worship_type' => 'non_muslim',
        ]);
        Sanctum::actingAs($student, ['student']);
        $payload = $this->payload($student);
        $payload['ibadah_details'] = [
            'subuh' => true,
            'dzuhur' => true,
            'ashar' => true,
            'maghrib' => true,
            'isya' => true,
        ];

        $this->postJson('/api/me/journal', $payload)
            ->assertUnprocessable()
            ->assertJsonPath('code', 'validation_error');

        $payload['ibadah_details'] = [
            'prayer' => true,
            'scripture' => true,
            'worship' => false,
            'spiritual_activity' => false,
            'other' => false,
        ];
        $this->postJson('/api/me/journal', $payload)
            ->assertCreated()
            ->assertJsonPath('data.beribadah', true);
    }

    private function student(array $overrides = []): User
    {
        return User::create(array_merge([
            'nis' => 'JOURNAL-001',
            'name' => 'Siswa Journal',
            'password' => Hash::make('secret123'),
            'role' => 'siswa',
            'kelas' => 'X PPLG',
            'worship_type' => 'muslim',
        ], $overrides));
    }

    private function payload(User $student): array
    {
        return [
            'bangun_pagi' => true,
            'bangun_pagi_time' => '05:30',
            'ibadah_details' => $student->worship_type === 'muslim'
                ? [
                    'subuh' => true,
                    'dzuhur' => true,
                    'ashar' => true,
                    'maghrib' => true,
                    'isya' => true,
                ]
                : [
                    'prayer' => true,
                    'scripture' => true,
                    'worship' => false,
                    'spiritual_activity' => false,
                    'other' => false,
                ],
            'berolahraga' => true,
            'olahraga_note' => 'Lari pagi',
            'makan_sehat' => true,
            'makan_note' => 'Sayur dan buah',
            'gemar_belajar' => true,
            'belajar_note' => 'Belajar matematika',
            'bermasyarakat' => true,
            'masyarakat_note' => 'Kerja bakti',
            'tidur_cepat' => true,
            'tidur_note' => '21:30',
        ];
    }

    /**
     * Regression test for bug: history card shows 7/7 but detail shows incomplete habits.
     * Ensures qualifiedHabits() with time validation is used consistently.
     */
    public function test_history_and_detail_use_qualified_habits_consistently(): void
    {
        $student = $this->student(['nis' => 'REGRESSION-001']);
        Sanctum::actingAs($student, ['student']);

        // Create a journal with bangun_pagi = true but INVALID time (12:00 - outside 03:00-10:00 range)
        // and beribadah = false (not all 5 prayers), gemar_belajar = false
        // Other 5 habits = true
        // Expected qualified count: 4 (bangun_pagi fails time validation, beribadah false, gemar_belajar false)
        $payload = $this->payload($student);
        $payload['bangun_pagi_time'] = '12:00'; // Invalid time
        $payload['gemar_belajar'] = false;
        // For Muslim, need all 5 prayers for beribadah
        $payload['ibadah_details'] = [
            'subuh' => false,
            'dzuhur' => false,
            'ashar' => false,
            'maghrib' => false,
            'isya' => false,
        ];

        $this->postJson('/api/me/journal', $payload)
            ->assertCreated()
            ->assertJsonPath('data.completed_count', 4)
            ->assertJsonPath('data.is_fully_completed', false)
            ->assertJsonPath('data.beribadah', false);

        // Fetch the journal detail via API (used by modal)
        $journal = Journal::where('user_id', $student->id)->latest('date')->first();
        $response = $this->getJson('/api/journal/'.$journal->date->toDateString())
            ->assertOk()
            ->assertJsonPath('found', true);

        // Verify qualified_habits in response matches expectation
        $qualified = $response->json('qualified_habits');
        $this->assertFalse($qualified['bangun_pagi']); // Invalid time
        $this->assertFalse($qualified['beribadah']); // Not all prayers
        $this->assertTrue($qualified['berolahraga']);
        $this->assertTrue($qualified['makan_sehat']);
        $this->assertFalse($qualified['gemar_belajar']);
        $this->assertTrue($qualified['bermasyarakat']);
        $this->assertTrue($qualified['tidur_cepat']);

        $this->assertEquals(4, $response->json('qualified_count'));
        $this->assertFalse($response->json('is_fully_qualified'));
    }

    /**
     * Regression test: stale data in database gets corrected by recalculation.
     * Simulates database with completed_count=7 but actual qualified habits < 7.
     */
    public function test_stale_data_gets_corrected_on_save(): void
    {
        $student = $this->student(['nis' => 'STALE-001']);
        Sanctum::actingAs($student, ['student']);

        // Create journal directly with stale data (completed_count=7 but beribadah=false)
        $journal = Journal::create([
            'user_id' => $student->id,
            'date' => now('Asia/Jakarta')->toDateString(),
            'bangun_pagi' => true,
            'bangun_pagi_time' => '05:30',
            'beribadah' => false, // Actually false
            'ibadah_details' => ['subuh' => false, 'dzuhur' => false, 'ashar' => false, 'maghrib' => false, 'isya' => false],
            'berolahraga' => true,
            'makan_sehat' => true,
            'gemar_belajar' => true,
            'bermasyarakat' => true,
            'tidur_cepat' => true,
            'tidur_note' => '21:30',
            'completed_count' => 7, // STALE: says 7 but only 6 qualified
            'is_fully_completed' => true, // STALE
            'is_submitted' => false,
        ]);

        // Update via API (auto-save) - should recalculate progress
        $payload = [
            'bangun_pagi' => true,
            'bangun_pagi_time' => '05:30',
            'ibadah_details' => ['subuh' => false, 'dzuhur' => false, 'ashar' => false, 'maghrib' => false, 'isya' => false],
            'berolahraga' => true,
            'makan_sehat' => true,
            'gemar_belajar' => true,
            'bermasyarakat' => true,
            'tidur_cepat' => true,
            'tidur_note' => '21:30',
        ];

        $this->putJson('/api/me/journal', $payload)
            ->assertOk()
            ->assertJsonPath('data.completed_count', 6) // Should be recalculated to 6
            ->assertJsonPath('data.is_fully_completed', false)
            ->assertJsonPath('data.beribadah', false);

        // Verify database was updated
        $this->assertDatabaseHas('journals', [
            'user_id' => $student->id,
            'completed_count' => 6,
            'is_fully_completed' => false,
        ]);
    }

    /**
     * Test that tidur_cepat time validation works in qualifiedHabits.
     */
    public function test_tidur_cepat_time_validation_in_qualified_habits(): void
    {
        $student = $this->student(['nis' => 'TIDUR-001']);
        Sanctum::actingAs($student, ['student']);

        // All habits true but tidur_cepat time is 19:00 (invalid - before 20:00)
        $payload = $this->payload($student);
        $payload['tidur_note'] = '19:00'; // Invalid time

        $this->postJson('/api/me/journal', $payload)
            ->assertCreated()
            ->assertJsonPath('data.completed_count', 6) // tidur_cepat fails validation
            ->assertJsonPath('data.is_fully_completed', false);

        $journal = Journal::where('user_id', $student->id)->latest('date')->first();
        $qualified = $journal->qualifiedHabits();
        $this->assertFalse($qualified['tidur_cepat']);
    }

    /**
     * Test Non-Muslim beribadah qualification (at least 1 activity).
     */
    public function test_non_muslim_beribadah_qualification(): void
    {
        $student = $this->student([
            'nis' => 'NON-MUSLIM-REG-001',
            'worship_type' => 'non_muslim',
        ]);
        Sanctum::actingAs($student, ['student']);

        // Only prayer = true, others false
        $payload = $this->payload($student);
        $payload['ibadah_details'] = [
            'prayer' => true,
            'scripture' => false,
            'worship' => false,
            'spiritual_activity' => false,
            'other' => false,
        ];

        $this->postJson('/api/me/journal', $payload)
            ->assertCreated()
            ->assertJsonPath('data.beribadah', true) // At least 1 = true
            ->assertJsonPath('data.completed_count', 7); // All 7 habits qualified

        // Now test with ALL false
        $payload2 = $this->payload($student);
        $payload2['ibadah_details'] = [
            'prayer' => false,
            'scripture' => false,
            'worship' => false,
            'spiritual_activity' => false,
            'other' => false,
        ];
        // Use different date
        $this->travel(1)->day();
        $this->postJson('/api/me/journal', $payload2)
            ->assertCreated()
            ->assertJsonPath('data.beribadah', false) // None selected = false
            ->assertJsonPath('data.completed_count', 6); // 6 other habits
    }

    /**
     * Test recalculation command works correctly.
     */
    public function test_recalculation_command_fixes_stale_data(): void
    {
        $student = $this->student(['nis' => 'CMD-001']);

        // Create journals with stale data directly
        Journal::create([
            'user_id' => $student->id,
            'date' => '2026-01-01',
            'bangun_pagi' => true,
            'bangun_pagi_time' => '12:00', // Invalid
            'beribadah' => false,
            'ibadah_details' => ['subuh' => false, 'dzuhur' => false, 'ashar' => false, 'maghrib' => false, 'isya' => false],
            'berolahraga' => true,
            'makan_sehat' => true,
            'gemar_belajar' => true,
            'bermasyarakat' => true,
            'tidur_cepat' => true,
            'tidur_note' => '21:30',
            'completed_count' => 7, // Stale
            'is_fully_completed' => true, // Stale
        ]);

        Journal::create([
            'user_id' => $student->id,
            'date' => '2026-01-02',
            'bangun_pagi' => true,
            'bangun_pagi_time' => '05:30',
            'beribadah' => true,
            'ibadah_details' => ['subuh' => true, 'dzuhur' => true, 'ashar' => true, 'maghrib' => true, 'isya' => true],
            'berolahraga' => true,
            'makan_sehat' => true,
            'gemar_belajar' => true,
            'bermasyarakat' => true,
            'tidur_cepat' => true,
            'tidur_note' => '21:30',
            'completed_count' => 7, // Correct
            'is_fully_completed' => true, // Correct
        ]);

        // Run command
        $this->artisan('journals:recalculate-progress')
            ->assertExitCode(0);

        // Verify first journal was fixed (5 qualified: bangun_pagi invalid, beribadah false)
        $this->assertDatabaseHas('journals', [
            'user_id' => $student->id,
            'date' => '2026-01-01',
            'completed_count' => 5,
            'is_fully_completed' => false,
        ]);

        // Verify second journal unchanged (7 qualified)
        $this->assertDatabaseHas('journals', [
            'user_id' => $student->id,
            'date' => '2026-01-02',
            'completed_count' => 7,
            'is_fully_completed' => true,
        ]);
    }
}
