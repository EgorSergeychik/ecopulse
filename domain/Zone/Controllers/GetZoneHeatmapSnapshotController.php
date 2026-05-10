<?php

namespace Domain\Zone\Controllers;

use App\Http\Controllers\Controller;
use App\Support\Traits\HasHttpResponses;
use Domain\Telemetry\Actions\BuildZoneHeatmapSnapshotAction;
use Domain\Zone\DTO\ZoneHeatmapSnapshotData;
use Domain\Zone\Models\Zone;
use Domain\Zone\Requests\GetZoneHeatmapSnapshotRequest;
use Illuminate\Http\JsonResponse;

class GetZoneHeatmapSnapshotController extends Controller
{
    use HasHttpResponses;

    public function __construct(
        private readonly BuildZoneHeatmapSnapshotAction $buildSnapshot,
    ) {
    }

    public function __invoke(GetZoneHeatmapSnapshotRequest $request, Zone $zone): JsonResponse
    {
        $data = ZoneHeatmapSnapshotData::fromRequest($request);

        $snapshot = ($this->buildSnapshot)($data);

        return $this->success($snapshot);
    }
}
