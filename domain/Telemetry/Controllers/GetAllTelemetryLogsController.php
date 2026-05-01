<?php

namespace Domain\Telemetry\Controllers;

use App\Http\Controllers\Controller;
use Domain\Telemetry\Actions\GetAllTelemetryLogsAction;
use Domain\Telemetry\DTO\TelemetryLogIndexData;
use Domain\Telemetry\Resources\TelemetryLogListResource;
use Illuminate\Http\Request;

class GetAllTelemetryLogsController extends Controller
{
    public function __construct(
        private readonly GetAllTelemetryLogsAction $getAllTelemetryLogs,
    ) {
    }

    public function __invoke(Request $request)
    {
        $data = TelemetryLogIndexData::fromRequest($request);
        $telemetryLogs = ($this->getAllTelemetryLogs)($data);

        return inertia('TelemetryLogs', [
            'telemetryLogs' => TelemetryLogListResource::collection($telemetryLogs),
            'filters' => [
                'search' => $data->search,
            ],
        ]);
    }
}
