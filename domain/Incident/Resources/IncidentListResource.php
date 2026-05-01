<?php

namespace Domain\Incident\Resources;

use Domain\Incident\Models\Incident;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Incident */
class IncidentListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'robot_name' => $this->robot?->name,
            'zone_name' => $this->zone?->name,
            'type' => $this->type,
            'type_label' => __('misc.incidents.types.'.$this->type),
            'severity' => $this->severity?->value,
            'severity_label' => __('misc.incidents.severity.'.$this->severity?->value),
            'description' => $this->description,
            'is_resolved' => (bool) $this->resolved_at,
            'resolved_at' => $this->resolved_at?->toDateTimeString(),
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
