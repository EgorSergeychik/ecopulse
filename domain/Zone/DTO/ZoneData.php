<?php

namespace Domain\Zone\DTO;

use Illuminate\Foundation\Http\FormRequest;

class ZoneData
{
    public function __construct(
        public string $name,
        public float $center_lat,
        public float $center_lng,
        public int $default_zoom,
        public ?array $polygon,
        public array $user_ids,
    ) {
    }

    public static function fromRequest(FormRequest $request): self
    {
        return new self(
            name: $request->name,
            center_lat: (float) $request->center_lat,
            center_lng: (float) $request->center_lng,
            default_zoom: (int) $request->default_zoom,
            polygon: $request->polygon ? json_decode($request->polygon, true) : null,
            user_ids: $request->user_ids ?? [],
        );
    }
}
