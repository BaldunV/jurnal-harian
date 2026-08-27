<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'nis' => trim((string) $this->input('nis')),
            'device_name' => trim((string) $this->input('device_name')),
        ]);
    }

    public function rules(): array
    {
        return [
            'nis' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'max:255'],
            'device_name' => ['required', 'string', 'min:2', 'max:100'],
        ];
    }
}
