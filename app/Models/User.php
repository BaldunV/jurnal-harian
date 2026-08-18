<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'nis',
        'name',
        'email',
        'password',
        'role',
        'kelas',
        'worship_type',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function journals()
    {
        return $this->hasMany(Journal::class);
    }

    /**
     * Metadata warna badge per jurusan.
     */
    public static function jurusanMeta(): array
    {
        return [
            'PPLG' => ['label' => 'PPLG', 'badge' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300'],
            'TJKT' => ['label' => 'TJKT', 'badge' => 'bg-sky-100 text-sky-700 dark:bg-sky-500/15 dark:text-sky-300'],
            'AKL' => ['label' => 'AKL',  'badge' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300'],
            'ACP' => ['label' => 'ACP',  'badge' => 'bg-rose-100 text-rose-700 dark:bg-rose-500/15 dark:text-rose-300'],
        ];
    }

    /**
     * Jurusan siswa diambil dari bagian belakang kolom kelas (X PPLG -> PPLG).
     */
    public function getJurusanAttribute()
    {
        $meta = static::jurusanMeta();
        $parts = explode(' ', trim((string) $this->kelas));
        $short = end($parts);

        return $meta[$short] ?? ['label' => $short ?: 'Siswa', 'badge' => 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300'];
    }

    /**
     * Hitung streak hari berturut-turut mengisi jurnal (fully completed 7/7).
     */
    public function getCurrentStreakAttribute()
    {
        $streak = 0;
        $checkDate = Carbon::today();

        // Cek jurnal hari ini. Jika belum diisi 7/7, cek mulai dari kemarin.
        $todayJournal = $this->journals()->whereDate('date', $checkDate)->first();
        if (! $todayJournal || ! $todayJournal->is_fully_completed) {
            $checkDate = Carbon::yesterday();
        }

        while (true) {
            $journal = $this->journals()->whereDate('date', $checkDate)->first();
            if ($journal && $journal->is_fully_completed) {
                $streak++;
                $checkDate->subDay();
            } else {
                break;
            }
        }

        return $streak;
    }

    /**
     * Dapatkan daftar lencana (badges) berdasarkan streak dan pencapaian.
     */
    public function getBadgesAttribute()
    {
        $streak = $this->current_streak;
        $totalFullJournals = $this->journals()->where('is_fully_completed', true)->count();

        $badges = [
            [
                'id' => 'starter',
                'name' => 'Langkah Perdana',
                'icon' => 'sprout',
                'desc' => 'Menyelesaikan 1 hari kebiasaan baik',
                'unlocked' => $totalFullJournals >= 1,
            ],
            [
                'id' => 'streak3',
                'name' => 'Pejuang Disiplin (3 Hari)',
                'icon' => 'flame',
                'desc' => 'Streak 3 hari berturut-turut',
                'unlocked' => $streak >= 3 || $totalFullJournals >= 3,
            ],
            [
                'id' => 'streak7',
                'name' => 'Bintang Sekolah (7 Hari)',
                'icon' => 'star',
                'desc' => 'Streak 7 hari berturut-turut',
                'unlocked' => $streak >= 7 || $totalFullJournals >= 7,
            ],
            [
                'id' => 'streak30',
                'name' => 'Pahlawan Kebiasaan (30 Hari)',
                'icon' => 'crown',
                'desc' => 'Streak 30 hari berturut-turut',
                'unlocked' => $streak >= 30 || $totalFullJournals >= 30,
            ],
        ];

        return $badges;
    }
}
