<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Carbon\Carbon;

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
     * Hitung streak hari berturut-turut mengisi jurnal (fully completed 7/7).
     */
    public function getCurrentStreakAttribute()
    {
        $streak = 0;
        $checkDate = Carbon::today();

        // Cek jurnal hari ini. Jika belum diisi 7/7, cek mulai dari kemarin.
        $todayJournal = $this->journals()->whereDate('date', $checkDate)->first();
        if (!$todayJournal || !$todayJournal->is_fully_completed) {
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
                'icon' => '🌱',
                'desc' => 'Menyelesaikan 1 hari 7 kebiasaan baik',
                'unlocked' => $totalFullJournals >= 1
            ],
            [
                'id' => 'streak3',
                'name' => 'Pejuang Disiplin (3 Hari)',
                'icon' => '🔥',
                'desc' => 'Streak 3 hari berturut-turut',
                'unlocked' => $streak >= 3 || $totalFullJournals >= 3
            ],
            [
                'id' => 'streak7',
                'name' => 'Bintang Sekolah (7 Hari)',
                'icon' => '⭐',
                'desc' => 'Streak 7 hari berturut-turut',
                'unlocked' => $streak >= 7 || $totalFullJournals >= 7
            ],
            [
                'id' => 'streak30',
                'name' => 'Pahlawan Kebiasaan (30 Hari)',
                'icon' => '👑',
                'desc' => 'Streak 30 hari berturut-turut',
                'unlocked' => $streak >= 30 || $totalFullJournals >= 30
            ]
        ];

        return $badges;
    }
}
