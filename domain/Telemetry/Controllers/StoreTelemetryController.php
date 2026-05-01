<?php

namespace Domain\Telemetry\Controllers;

use App\Http\Controllers\Controller;
use App\Support\Traits\HasHttpResponses;
use Domain\Robot\Models\Robot;
use Domain\Telemetry\Actions\StoreTelemetryAction;
use Domain\Telemetry\DTO\StoreTelemetryData;
use Domain\Telemetry\Requests\StoreTelemetryRequest;
use Illuminate\Http\JsonResponse;

class StoreTelemetryController extends Controller
{
    use HasHttpResponses;

    public function __construct(
        private readonly StoreTelemetryAction $storeTelemetry,
    ) {}

    public function __invoke(StoreTelemetryRequest $request): JsonResponse
    {
        $robot = $request->user();
        $data = StoreTelemetryData::fromRequest($request);

        if (! $robot instanceof Robot) {
            return $this->error('This endpoint is only available for robots.', 403);
        }

        [$telemetryLog, $incidents] = ($this->storeTelemetry)($robot, $data);

        return $this->success([
            'telemetry_log_id' => $telemetryLog->id,
            'incident_ids' => $incidents->pluck('id')->all(),
            'incident_count' => $incidents->count(),
        ], 'Telemetry stored.', 201);
    }
}
