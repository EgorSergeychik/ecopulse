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
        $keys = array_keys(config('telemetry.metrics'));

        return [
            'id'         => $this->id,
            'robot_name' => $this->robot?->name,
            'zone_name'  => $this->robot?->zone?->name,
            'lat'        => (float) $this->lat,
            'lng'        => (float) $this->lng,
            'metrics'    => collect($keys)
                ->mapWithKeys(fn (string $key) => [$key => data_get($this->metrics, $key)])
                ->filter(fn ($v) => $v !== null)
                ->all(),
            'recorded_at' => $this->recorded_at?->toDateTimeString(),
        ];
    }
}
