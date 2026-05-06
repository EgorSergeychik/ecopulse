<?php

namespace Domain\Telemetry\Actions;

use Domain\Telemetry\DTO\TelemetryLogIndexData;
use Domain\Telemetry\Models\TelemetryLog;
use Domain\Telemetry\Queries\TelemetryLogQueryBuilder;

class GetAllTelemetryLogsAction
{
    public function __invoke(TelemetryLogIndexData $data): TelemetryLogQueryBuilder
    {
        return TelemetryLog::query()
            ->checkAccess()
            ->when($data->search, fn (TelemetryLogQueryBuilder $query) => $query->search($data->search))
            ->when($data->zone_id, fn (TelemetryLogQueryBuilder $query) => $query->zoneId($data->zone_id))
            ->with(['robot:id,name,zone_id', 'robot.zone:id,name'])
            ->latest('recorded_at')
            ;
    }
}
