<?php

namespace Domain\Telemetry\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTelemetryRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'coords' => ['required', 'array'],
            'coords.lat' => ['required', 'numeric', 'between:-90,90'],
            'coords.lng' => ['required', 'numeric', 'between:-180,180'],

            'metrics' => ['required', 'array'],
            'metrics.battery_pct' => ['required', 'numeric', 'between:0,100'],
            'metrics.co2' => ['nullable', 'numeric', 'min:0'],
            'metrics.noise_level' => ['nullable', 'numeric', 'min:0'],

            'recorded_at' => ['nullable', 'date'],
        ];
    }
}
