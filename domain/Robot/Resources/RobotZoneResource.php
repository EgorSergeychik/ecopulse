<?php

namespace Domain\Robot\Resources;

use Domain\Robot\Models\Robot;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Robot */
class RobotZoneResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'status' => $this->status->getValue(),
            'status_label' => __('misc.robots.status.'.$this->status->getValue()),
            'battery_pct' => (float) $this->battery_pct,
            'lat' => $this->latestTelemetryLog?->lat ?? $this->zone?->center_lat,
            'lng' => $this->latestTelemetryLog?->lng ?? $this->zone?->center_lng,
            'latest_recorded_at' => $this->latestTelemetryLog?->recorded_at?->format('Y-m-d H:i:s'),
            'latest_metrics' => $this->latestTelemetryLog?->metrics ?? [],
        ];
    }
}
