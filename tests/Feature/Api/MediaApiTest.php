<?php

namespace Tests\Feature\Api;

use App\Models\Journal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MediaApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_journal_photos_are_private_replaceable_and_streamed_only_to_the_owner(): void
    {
        Storage::fake('local');
        Storage::fake('public');
        $student = $this->student(['nis' => 'MEDIA-OWNER']);
        $other = $this->student(['nis' => 'MEDIA-OTHER']);
        $journal = $this->journal($student);
        Sanctum::actingAs($student, ['student']);

        $this->post('/api/me/journals/'.$journal->id.'/photos/olahraga', [
            'photo' => UploadedFile::fake()->image('exercise.jpg', 300, 300),
        ], ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonMissingPath('data.olahraga_photo')
            ->assertJsonPath(
                'data.olahraga_photo_url',
                route('api.me.journals.photos.show', [$journal->id, 'olahraga']),
            );

        $oldPath = $journal->fresh()->olahraga_photo;
        $this->assertStringStartsWith(
            'journal-media/'.$student->id.'/'.$journal->id.'/olahraga/',
            $oldPath,
        );
        Storage::disk('local')->assertExists($oldPath);
        Storage::disk('public')->assertMissing($oldPath);

        $photoResponse = $this->get('/api/me/journals/'.$journal->id.'/photos/olahraga', [
            'Accept' => 'application/json',
        ])->assertOk();
        $this->assertStringContainsString(
            'private',
            (string) $photoResponse->headers->get('Cache-Control'),
        );
        $this->assertStringContainsString(
            'no-store',
            (string) $photoResponse->headers->get('Cache-Control'),
        );

        $this->post('/api/me/journals/'.$journal->id.'/photos/olahraga', [
            'photo' => UploadedFile::fake()->image('replacement.png', 320, 320),
        ], ['Accept' => 'application/json'])->assertOk();
        $newPath = $journal->fresh()->olahraga_photo;
        $this->assertNotSame($oldPath, $newPath);
        Storage::disk('local')->assertMissing($oldPath);
        Storage::disk('local')->assertExists($newPath);

        Sanctum::actingAs($other, ['student']);
        $this->getJson('/api/me/journals/'.$journal->id.'/photos/olahraga')
            ->assertNotFound()
            ->assertJsonPath('code', 'not_found');
        $this->post('/api/me/journals/'.$journal->id.'/photos/olahraga', [
            'photo' => UploadedFile::fake()->image('intruder.jpg', 100, 100),
        ], ['Accept' => 'application/json'])->assertNotFound();
    }

    public function test_photo_upload_validates_decoded_images_and_rejects_locked_journals(): void
    {
        Storage::fake('local');
        $student = $this->student();
        $journal = $this->journal($student);
        Sanctum::actingAs($student, ['student']);

        $this->post('/api/me/journals/'.$journal->id.'/photos/makan', [
            'photo' => UploadedFile::fake()->create('fake.jpg', 20, 'image/jpeg'),
        ], ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonPath('code', 'validation_error');

        $journal->update(['is_submitted' => true]);
        $this->post('/api/me/journals/'.$journal->id.'/photos/makan', [
            'photo' => UploadedFile::fake()->image('meal.jpg', 200, 200),
        ], ['Accept' => 'application/json'])
            ->assertConflict()
            ->assertJsonPath('code', 'journal_locked');

        $this->assertNull($journal->fresh()->makan_photo);
        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    public function test_authorized_media_stream_keeps_legacy_public_paths_readable(): void
    {
        Storage::fake('public');
        $student = $this->student();
        $journal = $this->journal($student);
        $legacyPath = 'journals/'.$journal->date->toDateString().'/olahraga.jpg';
        Storage::disk('public')->put($legacyPath, 'legacy-image');
        $journal->update(['olahraga_photo' => $legacyPath]);
        Sanctum::actingAs($student, ['student']);

        $this->get('/api/me/journals/'.$journal->id.'/photos/olahraga', [
            'Accept' => 'application/json',
        ])->assertOk()
            ->assertStreamedContent('legacy-image');
    }

    private function student(array $overrides = []): User
    {
        return User::create(array_merge([
            'nis' => 'MEDIA-001',
            'name' => 'Siswa Media',
            'password' => Hash::make('secret123'),
            'role' => 'siswa',
            'kelas' => 'X PPLG',
            'worship_type' => 'muslim',
        ], $overrides));
    }

    private function journal(User $student): Journal
    {
        return Journal::create([
            'user_id' => $student->id,
            'date' => now('Asia/Jakarta')->toDateString(),
        ]);
    }
}
