<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UploadPhotoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'photo' => [
                'required',
                'file',
                'image',
                'dimensions:min_width=1,min_height=1',
                'mimetypes:image/jpeg,image/png,image/webp',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
        ];
    }
}
