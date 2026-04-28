<?php

namespace Domain\Zone\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreZoneRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'center_lat' => [
                'required',
                'numeric',
                'between:-90,90',
            ],
            'center_lng' => [
                'required',
                'numeric',
                'between:-180,180',
            ],
            'default_zoom' => [
                'required',
                'integer',
                'between:1,22',
            ],
            'polygon' => [
                'nullable',
                'string',
            ],
            'thumbnail' => [
                'nullable',
                'image',
                'max:10240',
            ],
            'user_ids' => ['nullable', 'array'],
            'user_ids.*' => ['integer', 'exists:users,id'],
        ];
    }
}
