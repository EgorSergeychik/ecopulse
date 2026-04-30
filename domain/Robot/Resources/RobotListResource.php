<?php

namespace Domain\Robot\Resources;

use Domain\Robot\Models\Robot;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Robot */
class RobotListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'mac_address' => $this->mac_address,
            'zone_id' => $this->zone_id,
            'zone_name' => $this->zone?->name,
            'status' => $this->status->getValue(),
            'status_label' => __('misc.robots.status.'.$this->status->getValue()),
            'battery_pct' => (float) $this->battery_pct,
        ];
    }
}
