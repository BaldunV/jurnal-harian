<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JournalResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'date' => $this->date?->toDateString(),
            'bangun_pagi' => $this->bangun_pagi,
            'bangun_pagi_time' => $this->time($this->bangun_pagi_time),
            'beribadah' => $this->beribadah,
            'ibadah_details' => $this->ibadah_details ?? (object) [],
            'berolahraga' => $this->berolahraga,
            'olahraga_note' => $this->olahraga_note,
            'olahraga_photo_url' => $this->olahraga_photo
                ? route('api.me.journals.photos.show', [$this->id, 'olahraga'])
                : null,
            'makan_sehat' => $this->makan_sehat,
            'makan_note' => $this->makan_note,
            'makan_photo_url' => $this->makan_photo
                ? route('api.me.journals.photos.show', [$this->id, 'makan'])
                : null,
            'gemar_belajar' => $this->gemar_belajar,
            'belajar_note' => $this->belajar_note,
            'belajar_photo_url' => $this->belajar_photo
                ? route('api.me.journals.photos.show', [$this->id, 'belajar'])
                : null,
            'bermasyarakat' => $this->bermasyarakat,
            'masyarakat_note' => $this->masyarakat_note,
            'masyarakat_photo_url' => $this->masyarakat_photo
                ? route('api.me.journals.photos.show', [$this->id, 'masyarakat'])
                : null,
            'tidur_cepat' => $this->tidur_cepat,
            'tidur_note' => $this->time($this->tidur_note),
            'completed_count' => $this->completed_count,
            'is_fully_completed' => $this->is_fully_completed,
            'is_submitted' => $this->is_submitted,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }

    private function time(?string $value): ?string
    {
        return $value ? substr($value, 0, 5) : null;
    }
}
