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
        'ibadah_note',
        'berolahraga',
        'olahraga_note',
        'olahraga_photo',
        'makan_sehat',
        'makan_note',
        'makan_photo',
        'gemar_belajar',
        'belajar_note',
        'belajar_photo',
        'bermasyarakat',
        'masyarakat_note',
        'masyarakat_photo',
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

    protected $appends = [
        'olahraga_photo_url',
        'makan_photo_url',
        'belajar_photo_url',
        'masyarakat_photo_url',
    ];

    public function setDateAttribute($value)
    {
        $this->attributes['date'] = Carbon::parse($value)->toDateString();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getOlahragaPhotoUrlAttribute()
    {
        return $this->olahraga_photo ? asset('storage/'.$this->olahraga_photo) : null;
    }

    public function getMakanPhotoUrlAttribute()
    {
        return $this->makan_photo ? asset('storage/'.$this->makan_photo) : null;
    }

    public function getBelajarPhotoUrlAttribute()
    {
        return $this->belajar_photo
            ? asset('storage/'.$this->belajar_photo)
            : null;
    }

    public function getMasyarakatPhotoUrlAttribute()
    {
        return $this->masyarakat_photo ? asset('storage/'.$this->masyarakat_photo) : null;
    }

    /**
     * Jam bangun dianggap sesuai jika berada di rentang 03:00 - 10:00
     */
    public function isBangunPagiTimeValid(): bool
    {
        $time = $this->bangun_pagi_time;
        if (! $time || ! preg_match('/^(\d{2}):(\d{2})/', $time, $matches)) {
            return false;
        }

        $time = $matches[1].':'.$matches[2];

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
    public function qualifiedHabits(): array
    {
        return [
            'bangun_pagi' => $this->bangun_pagi && $this->isBangunPagiTimeValid(),
            'beribadah' => $this->beribadah,
            'berolahraga' => $this->berolahraga,
            'makan_sehat' => $this->makan_sehat,
            'gemar_belajar' => $this->gemar_belajar,
            'bermasyarakat' => $this->bermasyarakat,
            'tidur_cepat' => $this->tidur_cepat && $this->isTidurCepatTimeValid(),
        ];
    }

    public function recalculateProgress(): void
    {
        $completed = count(array_filter($this->qualifiedHabits()));

        $this->completed_count = $completed;
        $this->is_fully_completed = ($completed === 7);
    }
}
