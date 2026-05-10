<?php

namespace Domain\Zone\DTO;

use App\Support\Parents\ParentData;
use Domain\Zone\Models\Zone;
use Domain\Zone\Requests\GetZoneHeatmapSnapshotRequest;

class ZoneHeatmapSnapshotData extends ParentData
{
    public function __construct(
        public Zone $zone,
        public string $metric,
        public int $limit = 2000,
    ) {}

    public static function fromRequest(GetZoneHeatmapSnapshotRequest $request): self
    {
        return new self(
            zone: $request->zone,
            metric: (string) $request->string('metric'),
            limit: $request->integer('limit', 2000),
        );
    }
}
