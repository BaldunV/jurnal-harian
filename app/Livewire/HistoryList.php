<?php

namespace App\Livewire;

use App\Models\Journal;
use Livewire\Attributes\On;
use Livewire\Component;

class HistoryList extends Component
{
    #[On('journal-updated')]
    public function refreshHistory(): void
    {
        // render() otomatis melakukan query ulang database pada setiap request.
    }

    public function render()
    {
        $recentJournals = Journal::where('user_id', auth()->id())
            ->orderBy('date', 'desc')
            ->take(7)
            ->get();

        return view('livewire.history-list', [
            'recentJournals' => $recentJournals,
        ]);
    }
}
