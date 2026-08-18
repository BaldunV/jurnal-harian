<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Journal extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'bangun_pagi',
        'bangun_pagi_time',
        'beribadah',
        'ibadah_details',
        'berolahraga',
        'olahraga_note',
        'makan_sehat',
        'makan_note',
        'gemar_belajar',
        'belajar_note',
        'bermasyarakat',
        'masyarakat_note',
        'tidur_cepat',
        'tidur_note',
        'completed_count',
        'is_fully_completed',
        'is_submitted',
    ];

    protected $casts = [
        'date' => 'date:Y-m-d',
        'bangun_pagi' => 'boolean',
        'beribadah' => 'boolean',
        'ibadah_details' => 'array',
        'berolahraga' => 'boolean',
        'makan_sehat' => 'boolean',
        'gemar_belajar' => 'boolean',
        'bermasyarakat' => 'boolean',
        'tidur_cepat' => 'boolean',
        'is_fully_completed' => 'boolean',
        'is_submitted' => 'boolean',
    ];

    public function setDateAttribute($value)
    {
        $this->attributes['date'] = Carbon::parse($value)->toDateString();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Jam bangun dianggap sesuai jika berada di rentang 03:00 - 10:00
     */
    public function isBangunPagiTimeValid(): bool
    {
        $time = $this->bangun_pagi_time;
        if (! $time) {
            return false;
        }

        return $time >= '03:00' && $time <= '10:00';
    }

    /**
     * Jam tidur dianggap sesuai jika berada di rentang 20:00 - 23:59
     */
    public function isTidurCepatTimeValid(): bool
    {
        $note = $this->tidur_note;
        if (! $note) {
            return false;
        }

        if (! preg_match('/^(\d{2}):(\d{2})/', $note, $matches)) {
            return false;
        }

        $time = $matches[1].':'.$matches[2];

        return $time >= '20:00' && $time <= '23:59';
    }

    /**
     * Re-calculate completed_count and is_fully_completed before saving
     */
    public function recalculateProgress()
    {
        $habits = [
            $this->bangun_pagi && $this->isBangunPagiTimeValid(),
            $this->beribadah,
            $this->berolahraga,
            $this->makan_sehat,
            $this->gemar_belajar,
            $this->bermasyarakat,
            $this->tidur_cepat && $this->isTidurCepatTimeValid(),
        ];

        $completed = 0;
        foreach ($habits as $h) {
            if ($h) {
                $completed++;
            }
        }

        $this->completed_count = $completed;
        $this->is_fully_completed = ($completed === 7);
    }
}
