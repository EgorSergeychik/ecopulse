<?php

namespace Domain\Robot\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRobotRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'mac_address' => ['nullable', 'string', 'mac_address', 'max:255', 'unique:robots,mac_address'],
            'zone_id' => ['required', 'integer', 'exists:zones,id'],
        ];
    }
}
