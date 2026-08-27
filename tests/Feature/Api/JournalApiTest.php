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
            'doa_pagi' => true,
            'kitab_meditasi' => true,
            'doa_malam' => true,
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
                    'doa_pagi' => true,
                    'kitab_meditasi' => true,
                    'doa_malam' => true,
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
}
