<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class JournalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $notes = collect([
            'olahraga_note',
            'makan_note',
            'belajar_note',
            'masyarakat_note',
        ])->mapWithKeys(function (string $field): array {
            $value = $this->input($field);

            return [$field => is_string($value) ? trim($value) : $value];
        })->all();

        $this->merge($notes);
    }

    public function rules(): array
    {
        $muslim = $this->user()?->worship_type === 'muslim';
        $detailKeys = $muslim
            ? ['subuh', 'dzuhur', 'ashar', 'maghrib', 'isya']
            : ['prayer', 'scripture', 'worship', 'spiritual_activity', 'other'];

        $rules = [
            'bangun_pagi' => ['required', 'boolean'],
            'bangun_pagi_time' => ['nullable', 'required_if:bangun_pagi,true', 'date_format:H:i'],
            'ibadah_details' => ['required', 'array:'.implode(',', $detailKeys)],
            'ibadah_note' => ['nullable', 'string', 'max:500'],
            'berolahraga' => ['required', 'boolean'],
            'olahraga_note' => ['nullable', 'string', 'max:255'],
            'makan_sehat' => ['required', 'boolean'],
            'makan_note' => ['nullable', 'string', 'max:255'],
            'gemar_belajar' => ['required', 'boolean'],
            'belajar_note' => ['nullable', 'string', 'max:255'],
            'bermasyarakat' => ['required', 'boolean'],
            'masyarakat_note' => ['nullable', 'string', 'max:255'],
            'tidur_cepat' => ['required', 'boolean'],
            'tidur_note' => ['nullable', 'required_if:tidur_cepat,true', 'date_format:H:i'],
        ];

        foreach ($detailKeys as $key) {
            $rules['ibadah_details.'.$key] = ['required', 'boolean'];
        }

        foreach ($this->serverOwnedFields() as $field) {
            $rules[$field] = ['prohibited'];
        }

        return $rules;
    }

    private function serverOwnedFields(): array
    {
        return [
            'id',
            'user_id',
            'date',
            'beribadah',
            'olahraga_photo',
            'olahraga_photo_url',
            'makan_photo',
            'makan_photo_url',
            'belajar_photo',
            'belajar_photo_url',
            'masyarakat_photo',
            'masyarakat_photo_url',
            'completed_count',
            'is_fully_completed',
            'is_submitted',
            'created_at',
            'updated_at',
        ];
    }
}
