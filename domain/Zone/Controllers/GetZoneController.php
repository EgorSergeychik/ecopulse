<?php

namespace Domain\Zone\Controllers;

use App\Http\Controllers\Controller;
use Domain\Robot\Actions\GetAllRobotsAction;
use Domain\Robot\DTO\RobotIndexData;
use Domain\Robot\Resources\RobotZoneResource;
use Domain\Telemetry\Actions\GetAllTelemetryLogsAction;
use Domain\Telemetry\DTO\TelemetryLogIndexData;
use Domain\Telemetry\Resources\TelemetryLogListResource;
use Domain\Zone\Models\Zone;
use Domain\Zone\Resources\ZoneListResource;
use Illuminate\Http\Request;

class GetZoneController extends Controller
{
    public function __construct(
        private readonly GetAllRobotsAction $getRobots,
        private readonly GetAllTelemetryLogsAction $getTelemetryLogs,
    ) {
    }

    public function __invoke(Request $request, Zone $zone)
    {
        $zone->load('users:id');

        $robotsData = RobotIndexData::from(['zone_id' => $zone->id]);
        $robots = ($this->getRobots)($robotsData)->with('latestTelemetryLog')->get();

        $telemetryLogsData = TelemetryLogIndexData::from(['zone_id' => $zone->id, 'limit' => $request->integer('per_page', 10)]);
        $telemetryLogs = $telemetryLogsData->is_paginated
            ? ($this->getTelemetryLogs)($telemetryLogsData)->paginate($telemetryLogsData->limit)->withQueryString()
            : ($this->getTelemetryLogs)($telemetryLogsData)->get();

        return inertia('ZoneShow', [
            'zone' => ZoneListResource::make($zone),
            'robots' => RobotZoneResource::collection($robots),
            'telemetryLogs' => TelemetryLogListResource::collection($telemetryLogs),
        ]);
    }
}
