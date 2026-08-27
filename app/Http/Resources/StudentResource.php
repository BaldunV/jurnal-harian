<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nis' => $this->nis,
            'name' => $this->name,
            'class' => $this->kelas,
            'role' => $this->role,
            'worship_type' => $this->worship_type,
        ];
    }
}
