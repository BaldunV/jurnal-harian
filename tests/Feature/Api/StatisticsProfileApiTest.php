<?php

namespace Tests\Feature\Api;

use App\Models\Journal;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class StatisticsProfileApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_statistics_use_server_qualified_habits_and_ignore_other_students(): void
    {
        $this->travelTo(Carbon::parse('2026-08-28 05:00:00', 'Asia/Jakarta'));
        $student = $this->student(['nis' => 'STATS-OWNER']);
        $other = $this->student(['nis' => 'STATS-OTHER']);
        $today = now('Asia/Jakarta')->toDateString();
        $this->journal($student, $today, [
            'bangun_pagi' => true,
            'bangun_pagi_time' => '02:30',
            'beribadah' => true,
            'berolahraga' => true,
            'makan_sehat' => true,
            'gemar_belajar' => true,
            'bermasyarakat' => true,
            'tidur_cepat' => true,
            'tidur_note' => '00:30',
        ]);
        $this->journal($student, now('Asia/Jakarta')->subDay()->toDateString(), $this->completeHabits());
        $this->journal($student, now('Asia/Jakarta')->subDays(2)->toDateString(), $this->completeHabits());
        $this->journal($student, now('Asia/Jakarta')->subDays(8)->toDateString(), $this->completeHabits());
        $this->journal($other, $today, $this->completeHabits());
        Sanctum::actingAs($student, ['student']);

        $response = $this->getJson('/api/me/statistics?period=week')
            ->assertOk()
            ->assertJsonPath('data.period', 'week')
            ->assertJsonPath('data.today_progress', 5)
            ->assertJsonPath('data.today_total', 7)
            ->assertJsonPath('data.percentage', 90)
            ->assertJsonPath('data.current_streak', 2)
            ->assertJsonPath('data.completed_days', 2)
            ->assertJsonPath('data.recorded_days', 3)
            ->assertJsonPath('data.period_days', 7)
            ->assertJsonCount(7, 'data.habit_statistics');

        $habits = collect($response->json('data.habit_statistics'))->keyBy('key');
        $this->assertSame(2, $habits->get('bangun_pagi')['count']);
        $this->assertSame(67, $habits->get('bangun_pagi')['percentage']);
        $this->assertSame(3, $habits->get('beribadah')['count']);
        $this->assertSame(2, $habits->get('tidur_cepat')['count']);
    }

    public function test_statistics_return_real_zeroes_for_an_empty_period_and_validate_period(): void
    {
        Sanctum::actingAs($this->student(), ['student']);

        $this->getJson('/api/me/statistics?period=month')
            ->assertOk()
            ->assertJsonPath('data.today_progress', 0)
            ->assertJsonPath('data.percentage', 0)
            ->assertJsonPath('data.current_streak', 0)
            ->assertJsonPath('data.completed_days', 0)
            ->assertJsonPath('data.recorded_days', 0)
            ->assertJsonPath('data.period_days', 30);

        $this->getJson('/api/me/statistics?period=year')
            ->assertUnprocessable()
            ->assertJsonPath('code', 'validation_error');
    }

    public function test_profile_updates_are_whitelisted_and_sensitive_fields_are_never_returned(): void
    {
        $student = $this->student(['nis' => 'PROFILE-001']);
        Sanctum::actingAs($student, ['student']);

        $this->getJson('/api/me/profile')
            ->assertOk()
            ->assertJsonPath('data.nis', 'PROFILE-001')
            ->assertJsonMissingPath('data.religion')
            ->assertJsonMissingPath('data.password')
            ->assertJsonMissingPath('data.profile_photo');

        $this->putJson('/api/me/profile', [
            'name' => 'Nama Baru',
            'worship_type' => 'non_muslim',
            'nis' => 'TAKEOVER',
            'role' => 'admin',
        ])->assertUnprocessable()
            ->assertJsonPath('code', 'validation_error');
        $student->refresh();
        $this->assertSame('PROFILE-001', $student->nis);
        $this->assertSame('siswa', $student->role);
        $this->assertSame('Siswa API', $student->name);

        $this->putJson('/api/me/profile', [
            'name' => 'Nama Baru',
            'worship_type' => 'non_muslim',
        ])->assertOk()
            ->assertJsonPath('data.name', 'Nama Baru')
            ->assertJsonPath('data.worship_type', 'muslim');

        $this->putJson('/api/me/profile', [
            'name' => 'Nama Baru',
            'worship_type' => 'muslim',
            'religion' => 'hindu',
        ])->assertUnprocessable()
            ->assertJsonPath('code', 'validation_error');
    }

    public function test_password_change_requires_the_current_password_and_revokes_all_credentials(): void
    {
        $student = $this->student(['nis' => 'PASSWORD-001']);
        $currentToken = $student->createToken('Current', ['student'])->plainTextToken;
        $student->createToken('Other', ['student']);

        $this->withToken($currentToken)->postJson('/api/me/change-password', [
            'current_password' => 'wrong-password',
            'password' => 'NewPassword1!',
            'password_confirmation' => 'NewPassword1!',
        ])->assertUnprocessable()
            ->assertJsonPath('code', 'current_password_invalid');
        $this->assertDatabaseCount('personal_access_tokens', 2);

        $this->withToken($currentToken)->postJson('/api/me/change-password', [
            'current_password' => 'secret123',
            'password' => 'NewPassword1!',
            'password_confirmation' => 'NewPassword1!',
        ])->assertOk()
            ->assertJsonPath('data.reauthentication_required', true);

        $this->assertTrue(Hash::check('NewPassword1!', $student->fresh()->password));
        $this->assertDatabaseCount('personal_access_tokens', 0);
        $this->withToken($currentToken)->getJson('/api/me/profile')
            ->assertUnauthorized()
            ->assertJsonPath('code', 'unauthenticated');
    }

    public function test_profile_photos_use_private_storage_and_authorized_streaming(): void
    {
        Storage::fake('local');
        $student = $this->student(['nis' => 'PROFILE-PHOTO']);
        Sanctum::actingAs($student, ['student']);

        $this->post('/api/me/profile/photo', [
            'photo' => UploadedFile::fake()->image('avatar.jpg', 300, 300),
        ], ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJsonPath('data.profile_photo_url', route('api.me.profile.photo.show'))
            ->assertJsonMissingPath('data.profile_photo');

        $path = $student->fresh()->profile_photo;
        $this->assertStringStartsWith('profile-media/'.$student->id.'/', $path);
        Storage::disk('local')->assertExists($path);
        $this->get('/api/me/profile/photo')->assertOk();
    }

    private function student(array $overrides = []): User
    {
        return User::create(array_merge([
            'nis' => 'STATS-001',
            'name' => 'Siswa API',
            'password' => Hash::make('secret123'),
            'role' => 'siswa',
            'kelas' => 'X PPLG',
            'worship_type' => 'muslim',
        ], $overrides));
    }

    private function journal(User $student, string $date, array $habits): Journal
    {
        $journal = new Journal(array_merge([
            'date' => $date,
            'bangun_pagi' => false,
            'beribadah' => false,
            'berolahraga' => false,
            'makan_sehat' => false,
            'gemar_belajar' => false,
            'bermasyarakat' => false,
            'tidur_cepat' => false,
        ], $habits));
        $journal->user()->associate($student);
        $journal->recalculateProgress();
        $journal->save();

        return $journal;
    }

    private function completeHabits(): array
    {
        return [
            'bangun_pagi' => true,
            'bangun_pagi_time' => '05:30',
            'beribadah' => true,
            'berolahraga' => true,
            'makan_sehat' => true,
            'gemar_belajar' => true,
            'bermasyarakat' => true,
            'tidur_cepat' => true,
            'tidur_note' => '21:30',
        ];
    }
}
