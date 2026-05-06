<?php

namespace Domain\Zone\Resources;

use Domain\Zone\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Zone */
class ZoneResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'center_lat' => $this->center_lat,
            'center_lng' => $this->center_lng,
            'default_zoom' => $this->default_zoom,
            'polygon' => $this->bounding_box,
        ];
    }
}
