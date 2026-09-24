<?php

namespace Tests\Feature;

use App\Livewire\HistoryList;
use App\Livewire\JournalForm;
use App\Models\Journal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class JournalHistoryLivewireTest extends TestCase
{
    use RefreshDatabase;

    public function test_journal_form_dispatches_journal_updated_after_successful_save(): void
    {
        $student = $this->student();
        $journal = Journal::create([
            'user_id' => $student->id,
            'date' => now()->toDateString(),
        ]);

        Livewire::actingAs($student)
            ->test(JournalForm::class, ['journal' => $journal, 'user' => $student])
            ->call('saveFromClient', $this->formData())
            ->assertDispatched('journal-updated');
    }

    public function test_history_requeries_database_when_journal_updated_is_dispatched(): void
    {
        $student = $this->student();
        $journal = Journal::create([
            'user_id' => $student->id,
            'date' => now()->toDateString(),
            'bangun_pagi' => true,
            'bangun_pagi_time' => '05:30',
            'beribadah' => false,
            'berolahraga' => true,
            'makan_sehat' => true,
            'gemar_belajar' => false,
            'bermasyarakat' => true,
            'tidur_cepat' => true,
            'tidur_note' => '21:30',
        ]);
        $journal->recalculateProgress();
        $journal->save();

        $component = Livewire::actingAs($student)
            ->test(HistoryList::class)
            ->assertSee('5/7 Kebiasaan Terisi');

        $journal->beribadah = true;
        $journal->recalculateProgress();
        $journal->save();

        $component->dispatch('journal-updated')
            ->assertSee('6/7 Kebiasaan Terisi')
            ->assertDontSee('5/7 Kebiasaan Terisi');
    }

    private function student(): User
    {
        return User::create([
            'nis' => 'LIVEWIRE-001',
            'name' => 'Siswa Livewire',
            'password' => Hash::make('secret123'),
            'role' => 'siswa',
            'kelas' => 'X PPLG',
            'worship_type' => 'non_muslim',
        ]);
    }

    private function formData(): array
    {
        return [
            'date' => now()->toDateString(),
            'bangun_pagi' => true,
            'bangun_pagi_time' => '05:30',
            'ibadah_prayer' => true,
            'ibadah_scripture' => false,
            'ibadah_worship' => false,
            'ibadah_spiritual_activity' => false,
            'ibadah_other' => false,
            'berolahraga' => true,
            'makan_sehat' => true,
            'gemar_belajar' => true,
            'bermasyarakat' => true,
            'tidur_cepat' => true,
            'tidur_note' => '21:30',
        ];
    }
}
