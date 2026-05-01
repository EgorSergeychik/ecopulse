<?php

namespace Domain\Telemetry\Resources;

use Domain\Telemetry\Models\TelemetryLog;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin TelemetryLog */
class TelemetryLogListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'robot_name' => $this->robot?->name,
            'zone_name' => $this->robot?->zone?->name,
            'lat' => (float) $this->lat,
            'lng' => (float) $this->lng,
            'battery_pct' => data_get($this->metrics, 'battery_pct'),
            'co2' => data_get($this->metrics, 'co2'),
            'noise_level' => data_get($this->metrics, 'noise_level'),
            'metrics' => $this->metrics,
            'recorded_at' => $this->recorded_at?->toDateTimeString(),
        ];
    }
}
