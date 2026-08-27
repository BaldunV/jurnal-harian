<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StatisticsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'period' => $this->input('period', 'week'),
        ]);
    }

    public function rules(): array
    {
        return [
            'period' => ['required', 'in:week,month'],
        ];
    }
}
