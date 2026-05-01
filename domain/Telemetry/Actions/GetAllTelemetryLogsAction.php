<?php

namespace Domain\Telemetry\Actions;

use Domain\Telemetry\DTO\TelemetryLogIndexData;
use Domain\Telemetry\Models\TelemetryLog;
use Domain\Telemetry\Queries\TelemetryLogQueryBuilder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GetAllTelemetryLogsAction
{
    public function __invoke(TelemetryLogIndexData $data): LengthAwarePaginator
    {
        return TelemetryLog::query()
            ->checkAccess()
            ->when($data->search, fn (TelemetryLogQueryBuilder $query) => $query->search($data->search))
            ->with('robot.zone:id,name')
            ->latest('recorded_at')
            ->paginate($data->limit)
            ->withQueryString();
    }
}
