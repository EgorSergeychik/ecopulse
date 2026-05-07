<?php

namespace Domain\Telemetry\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTelemetryRequest extends FormRequest
{
    public function rules(): array
    {
        $rules = [
            'coords'      => ['required', 'array'],
            'coords.lat'  => ['required', 'numeric', 'between:-90,90'],
            'coords.lng'  => ['required', 'numeric', 'between:-180,180'],
            'metrics'     => ['required', 'array'],
            'recorded_at' => ['nullable', 'date'],
        ];

        foreach (config('telemetry.metrics') as $key => $def) {
            $rules["metrics.{$key}"] = [
                $def['required'] ? 'required' : 'nullable',
                ...$def['validation'],
            ];
        }

        return $rules;
    }
}
