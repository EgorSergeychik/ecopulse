<?php

namespace Domain\Telemetry\DTO;

use Domain\Telemetry\Requests\StoreTelemetryRequest;
use Illuminate\Support\Carbon;

class StoreTelemetryData
{
    public function __construct(
        public float $lat,
        public float $lng,
        public array $metrics,
        public ?Carbon $recorded_at = null,
    ) {}

    public static function fromRequest(StoreTelemetryRequest $request): self
    {
        return new self(
            lat: (float) $request->input('coords.lat'),
            lng: (float) $request->input('coords.lng'),
            metrics: $request->input('metrics', []),
            recorded_at: $request->filled('recorded_at') ? Carbon::parse($request->input('recorded_at')) : null,
        );
    }
}
