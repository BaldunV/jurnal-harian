<?php

namespace App\Services;

use App\Models\Journal;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class JournalStatisticsService
{
    private const HABITS = [
        'bangun_pagi' => ['name' => 'Bangun Pagi', 'icon' => 'sunrise'],
        'beribadah' => ['name' => 'Beribadah', 'icon' => 'hand-heart'],
        'berolahraga' => ['name' => 'Berolahraga', 'icon' => 'footprints'],
        'makan_sehat' => ['name' => 'Makan Sehat', 'icon' => 'salad'],
        'gemar_belajar' => ['name' => 'Gemar Belajar', 'icon' => 'book-open'],
        'bermasyarakat' => ['name' => 'Bermasyarakat', 'icon' => 'handshake'],
        'tidur_cepat' => ['name' => 'Tidur Cepat', 'icon' => 'moon-star'],
    ];

    public function forPeriod(User $user, string $period): array
    {
        $periodDays = $period === 'week' ? 7 : 30;
        $today = CarbonImmutable::now(JournalService::TIMEZONE)->startOfDay();
        $start = $today->subDays($periodDays - 1);
        $journals = $user->journals()
            ->whereBetween('date', [$start->toDateString(), $today->toDateString()])
            ->orderBy('date')
            ->get();
        $qualified = $journals->mapWithKeys(
            fn (Journal $journal): array => [$journal->date->toDateString() => $journal->qualifiedHabits()],
        );
        $recordedDays = $journals->count();
        $completedSlots = $qualified->sum(fn (array $habits): int => count(array_filter($habits)));
        $todayHabits = $qualified->get($today->toDateString(), []);

        return [
            'period' => $period,
            'today_progress' => count(array_filter($todayHabits)),
            'today_total' => count(self::HABITS),
            'percentage' => $recordedDays === 0
                ? 0
                : (int) round(($completedSlots / ($recordedDays * count(self::HABITS))) * 100),
            'current_streak' => $this->currentStreak($qualified, $today, $start),
            'completed_days' => $qualified->filter(
                fn (array $habits): bool => count(array_filter($habits)) === count(self::HABITS),
            )->count(),
            'recorded_days' => $recordedDays,
            'period_days' => $periodDays,
            'habit_statistics' => $this->habitStatistics($qualified, $recordedDays),
        ];
    }

    private function currentStreak(Collection $qualified, CarbonImmutable $today, CarbonImmutable $start): int
    {
        $cursor = $today;
        $todayHabits = $qualified->get($today->toDateString());

        if (! $this->isComplete($todayHabits)) {
            $cursor = $cursor->subDay();
        }

        $streak = 0;
        while ($cursor->greaterThanOrEqualTo($start)) {
            if (! $this->isComplete($qualified->get($cursor->toDateString()))) {
                break;
            }

            $streak++;
            $cursor = $cursor->subDay();
        }

        return $streak;
    }

    private function habitStatistics(Collection $qualified, int $recordedDays): array
    {
        return collect(self::HABITS)->map(function (array $meta, string $key) use ($qualified, $recordedDays): array {
            $count = $qualified->filter(fn (array $habits): bool => $habits[$key])->count();

            return [
                'key' => $key,
                'name' => $meta['name'],
                'icon' => $meta['icon'],
                'count' => $count,
                'percentage' => $recordedDays === 0 ? 0 : (int) round(($count / $recordedDays) * 100),
            ];
        })->values()->all();
    }

    private function isComplete(?array $habits): bool
    {
        return $habits !== null && count(array_filter($habits)) === count(self::HABITS);
    }
}
