<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim((string) $this->input('name')),
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'worship_type' => ['required', 'in:muslim,non_muslim'],
            'id' => ['prohibited'],
            'nis' => ['prohibited'],
            'email' => ['prohibited'],
            'role' => ['prohibited'],
            'kelas' => ['prohibited'],
            'class' => ['prohibited'],
            'phone' => ['prohibited'],
            'otp_channel' => ['prohibited'],
            'password' => ['prohibited'],
            'profile_photo' => ['prohibited'],
        ];
    }
}
