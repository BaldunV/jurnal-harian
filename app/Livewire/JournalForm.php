<?php

namespace App\Livewire;

use App\Models\Journal;
use App\Models\User;
use Carbon\Carbon;
use Livewire\Component;

class JournalForm extends Component
{
    public Journal $journal;

    public User $user;

    public function mount(Journal $journal, User $user)
    {
        $this->journal = $journal;
        $this->user = $user;
    }

    /**
     * Simpan jurnal dari data form (dipanggil JS via Livewire).
     * Mengembalikan statistik untuk update UI (progress, streak).
     */
    public function saveFromClient(array $data): array
    {
        $user = auth()->user();
        $targetDateStr = Carbon::parse($data['date'] ?? $this->journal->date->toDateString())->toDateString();

        $journal = Journal::where('user_id', $user->id)
            ->whereDate('date', $targetDateStr)
            ->first();

        // Jurnal yang sudah di-submit tidak dapat diubah kembali selamanya
        if ($journal && $journal->is_submitted) {
            return [
                'success' => false,
                'is_locked' => true,
                'message' => 'Jurnal telah disimpan permanen dan tidak dapat diubah kembali selamanya.',
            ];
        }

        if (! $journal) {
            $journal = new Journal;
            $journal->user_id = $user->id;
            $journal->date = $targetDateStr;
        }

        $journal->bangun_pagi = ! empty($data['bangun_pagi']);
        $journal->bangun_pagi_time = $journal->bangun_pagi ? ($data['bangun_pagi_time'] ?? null) : null;
        $journal->berolahraga = ! empty($data['berolahraga']);
        $journal->olahraga_note = $data['olahraga_note'] ?? null;
        $journal->makan_sehat = ! empty($data['makan_sehat']);
        $journal->makan_note = $data['makan_note'] ?? null;
        $journal->gemar_belajar = ! empty($data['gemar_belajar']);
        $journal->belajar_note = $data['belajar_note'] ?? null;
        $journal->bermasyarakat = ! empty($data['bermasyarakat']);
        $journal->masyarakat_note = $data['masyarakat_note'] ?? null;
        $journal->tidur_cepat = ! empty($data['tidur_cepat']);
        $journal->tidur_note = $data['tidur_note'] ?? null;

        // Processing Ibadah
        if ($user->worship_type === 'muslim') {
            $prayers = [
                'subuh' => ! empty($data['ibadah_subuh']),
                'dzuhur' => ! empty($data['ibadah_dzuhur']),
                'ashar' => ! empty($data['ibadah_ashar']),
                'maghrib' => ! empty($data['ibadah_maghrib']),
                'isya' => ! empty($data['ibadah_isya']),
            ];

            $journal->ibadah_details = $prayers;
            $journal->beribadah = ($prayers['subuh'] && $prayers['dzuhur'] && $prayers['ashar'] && $prayers['maghrib'] && $prayers['isya']);
        } else {
            $prayers = [
                'doa_pagi' => ! empty($data['ibadah_doa_pagi']),
                'kitab_meditasi' => ! empty($data['ibadah_kitab']),
                'doa_malam' => ! empty($data['ibadah_doa_malam']),
            ];

            $journal->ibadah_details = $prayers;
            $journal->beribadah = ($prayers['doa_pagi'] && $prayers['kitab_meditasi'] && $prayers['doa_malam']);
        }

        $journal->recalculateProgress();
        $journal->save();

        $this->journal = $journal;

        return [
            'success' => true,
            'message' => 'Jurnal berhasil diperbarui!',
            'completed_count' => $journal->completed_count,
            'is_fully_completed' => $journal->is_fully_completed,
            'is_submitted' => $journal->is_submitted,
            'beribadah' => $journal->beribadah,
            'streak' => $user->current_streak,
        ];
    }

    /**
     * Simpan & kunci jurnal permanen (tombol "Simpan Jurnal Hari Ini").
     * Redirect kembali ke dashboard dengan pesan sukses.
     */
    public function saveAndLock(array $data)
    {
        $result = $this->saveFromClient($data);

        if (empty($result['success'])) {
            return $result;
        }

        $journal = $this->journal;
        $journal->is_submitted = true;
        $journal->save();

        return redirect()->route('dashboard')->with('success', 'Jurnal harian berhasil disimpan dan dikunci permanen!');
    }

    public function render()
    {
        return view('livewire.journal-form');
    }
}
