<?php

namespace App\Services;

use App\Exceptions\ApiException;
use App\Models\Journal;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class JournalService
{
    public const TIMEZONE = 'Asia/Jakarta';

    public function todayDate(): string
    {
        return now(self::TIMEZONE)->toDateString();
    }

    public function findToday(User $user): ?Journal
    {
        return $user->journals()
            ->where('date', $this->todayDate())
            ->first();
    }

    public function createToday(User $user, array $data): Journal
    {
        $date = $this->todayDate();

        try {
            return DB::transaction(function () use ($user, $data, $date): Journal {
                $exists = $user->journals()
                    ->where('date', $date)
                    ->lockForUpdate()
                    ->exists();

                if ($exists) {
                    $this->alreadyExists();
                }

                $journal = $user->journals()->make(['date' => $date]);

                return $this->saveData($journal, $user, $data);
            });
        } catch (QueryException $exception) {
            if ($user->journals()->where('date', $date)->exists()) {
                $this->alreadyExists();
            }

            throw $exception;
        }
    }

    public function updateToday(User $user, array $data): Journal
    {
        return $this->mutateToday($user, function (Journal $journal) use ($user, $data): Journal {
            return $this->saveData($journal, $user, $data);
        });
    }

    public function submitToday(User $user, array $data): Journal
    {
        return $this->mutateToday($user, function (Journal $journal) use ($user, $data): Journal {
            $journal = $this->saveData($journal, $user, $data, save: false);
            $journal->is_submitted = true;
            $journal->save();

            return $journal->refresh();
        });
    }

    private function mutateToday(User $user, callable $mutation): Journal
    {
        return DB::transaction(function () use ($user, $mutation): Journal {
            $journal = $user->journals()
                ->where('date', $this->todayDate())
                ->lockForUpdate()
                ->first();

            if (! $journal) {
                throw new ApiException(
                    'Jurnal hari ini belum dibuat.',
                    404,
                    'journal_not_found',
                );
            }

            $this->ensureMutable($journal);

            return $mutation($journal);
        });
    }

    private function saveData(
        Journal $journal,
        User $user,
        array $data,
        bool $save = true,
    ): Journal {
        $details = $this->worshipDetails($user, $data['ibadah_details']);

        $journal->forceFill([
            'bangun_pagi' => $data['bangun_pagi'],
            'bangun_pagi_time' => $data['bangun_pagi'] ? $data['bangun_pagi_time'] : null,
            'ibadah_details' => $details,
            'beribadah' => ! in_array(false, $details, true),
            'berolahraga' => $data['berolahraga'],
            'olahraga_note' => $this->nullableString($data['olahraga_note'] ?? null),
            'makan_sehat' => $data['makan_sehat'],
            'makan_note' => $this->nullableString($data['makan_note'] ?? null),
            'gemar_belajar' => $data['gemar_belajar'],
            'belajar_note' => $this->nullableString($data['belajar_note'] ?? null),
            'bermasyarakat' => $data['bermasyarakat'],
            'masyarakat_note' => $this->nullableString($data['masyarakat_note'] ?? null),
            'tidur_cepat' => $data['tidur_cepat'],
            'tidur_note' => $data['tidur_cepat'] ? $data['tidur_note'] : null,
        ]);
        $journal->recalculateProgress();

        if ($save) {
            $journal->save();

            return $journal->refresh();
        }

        return $journal;
    }

    private function worshipDetails(User $user, array $details): array
    {
        $keys = $user->worship_type === 'muslim'
            ? ['subuh', 'dzuhur', 'ashar', 'maghrib', 'isya']
            : ['doa_pagi', 'kitab_meditasi', 'doa_malam'];

        return collect($keys)
            ->mapWithKeys(fn (string $key): array => [$key => (bool) $details[$key]])
            ->all();
    }

    private function nullableString(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function ensureMutable(Journal $journal): void
    {
        if ($journal->is_submitted) {
            throw new ApiException(
                'Jurnal yang sudah dikirim tidak dapat diubah.',
                409,
                'journal_locked',
            );
        }
    }

    private function alreadyExists(): never
    {
        throw new ApiException(
            'Jurnal hari ini sudah dibuat.',
            409,
            'journal_exists',
        );
    }
}
