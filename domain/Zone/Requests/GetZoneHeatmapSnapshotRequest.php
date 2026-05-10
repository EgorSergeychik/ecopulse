<?php

namespace Domain\Zone\Requests;

use Domain\Zone\Models\Zone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @property-read Zone $zone
 */
class GetZoneHeatmapSnapshotRequest extends FormRequest
{
    private const EXCLUDED_METRICS = [
        'battery_pct',
    ];

    public function rules(): array
    {
        $metrics = collect(array_keys(config('telemetry.metrics')))
            ->reject(fn (string $metric) => in_array($metric, self::EXCLUDED_METRICS, true))
            ->values();

        return [
            'metric' => ['required', 'string', Rule::in($metrics)],
            'limit' => ['nullable', 'integer', 'min:100', 'max:5000'],
        ];
    }
}
