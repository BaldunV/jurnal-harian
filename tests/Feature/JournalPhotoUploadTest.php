<?php

namespace Tests\Feature;

use App\Livewire\JournalForm;
use App\Models\Journal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class JournalPhotoUploadTest extends TestCase
{
    use RefreshDatabase;

    private User $siswa;

    private Journal $journal;

    protected function setUp(): void
    {
        parent::setUp();

        $this->siswa = User::create([
            'nis' => 'SIS001', 'name' => 'Siswa Test', 'password' => Hash::make('secret123'),
            'role' => 'siswa', 'kelas' => 'X PPLG', 'worship_type' => 'non_muslim',
        ]);

        $this->journal = Journal::create([
            'user_id' => $this->siswa->id,
            'date' => now()->toDateString(),
        ]);
    }

    public function test_photo_upload_stores_file_and_sets_column(): void
    {
        Storage::fake('public');

        $component = Livewire::actingAs($this->siswa)
            ->test(JournalForm::class, ['journal' => $this->journal, 'user' => $this->siswa])
            ->set('olahragaPhoto', UploadedFile::fake()->image('olahraga.jpg', 200, 200))
            ->call('saveOlahragaPhoto');

        $component->assertHasNoErrors();

        $this->journal->refresh();
        $this->assertNotNull($this->journal->olahraga_photo);
        Storage::disk('public')->assertExists($this->journal->olahraga_photo);

        // Mengganti foto: file lama dihapus lalu disimpan ulang di path yang sama
        $oldPath = $this->journal->olahraga_photo;
        $component->set('olahragaPhoto', UploadedFile::fake()->image('olahraga2.jpg', 200, 200))
            ->call('saveOlahragaPhoto');

        $this->journal->refresh();
        $this->assertSame($oldPath, $this->journal->olahraga_photo);
        Storage::disk('public')->assertExists($this->journal->olahraga_photo);
    }

    public function test_photo_remove_deletes_file_and_clears_column(): void
    {
        Storage::fake('public');

        Livewire::actingAs($this->siswa)
            ->test(JournalForm::class, ['journal' => $this->journal, 'user' => $this->siswa])
            ->set('makanPhoto', UploadedFile::fake()->image('makan.jpg', 200, 200))
            ->call('saveMakanPhoto');

        $this->journal->refresh();
        $this->assertNotNull($this->journal->makan_photo);

        Livewire::actingAs($this->siswa)
            ->test(JournalForm::class, ['journal' => $this->journal, 'user' => $this->siswa])
            ->call('removeMakanPhoto');

        $this->journal->refresh();
        $this->assertNull($this->journal->makan_photo);
        Storage::disk('public')->assertMissing('journals/'.$this->journal->date->toDateString().'/makan.jpg');
    }

    public function test_photo_upload_rejected_when_journal_locked(): void
    {
        Storage::fake('public');

        $this->journal->update(['is_submitted' => true]);

        Livewire::actingAs($this->siswa)
            ->test(JournalForm::class, ['journal' => $this->journal, 'user' => $this->siswa])
            ->set('olahragaPhoto', UploadedFile::fake()->image('x.jpg', 100, 100))
            ->call('saveOlahragaPhoto')
            ->assertReturned(fn ($result) => $result['is_locked'] === true);

        $this->journal->refresh();
        $this->assertNull($this->journal->olahraga_photo);
    }

    public function test_journal_api_exposes_photo_url(): void
    {
        Storage::fake('public');

        Livewire::actingAs($this->siswa)
            ->test(JournalForm::class, ['journal' => $this->journal, 'user' => $this->siswa])
            ->set('olahragaPhoto', UploadedFile::fake()->image('olahraga.jpg', 200, 200))
            ->call('saveOlahragaPhoto');

        $this->journal->refresh();

        $response = $this->actingAs($this->siswa)
            ->getJson('/api/journal/'.$this->journal->date->toDateString());

        $response->assertOk()
            ->assertJsonPath('found', true)
            ->assertJsonPath('journal.olahraga_photo', $this->journal->olahraga_photo)
            ->assertJsonPath('journal.olahraga_photo_url', $this->journal->olahraga_photo_url);
    }
}
