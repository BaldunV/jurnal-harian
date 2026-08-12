<?php

namespace App\Models;

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
        $this->attributes['date'] = \Carbon\Carbon::parse($value)->toDateString();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Re-calculate completed_count and is_fully_completed before saving
     */
    public function recalculateProgress()
    {
        $habits = [
            $this->bangun_pagi,
            $this->beribadah,
            $this->berolahraga,
            $this->makan_sehat,
            $this->gemar_belajar,
            $this->bermasyarakat,
            $this->tidur_cepat,
        ];

        $completed = 0;
        foreach ($habits as $h) {
            if ($h) $completed++;
        }

        $this->completed_count = $completed;
        $this->is_fully_completed = ($completed === 7);
    }
}
