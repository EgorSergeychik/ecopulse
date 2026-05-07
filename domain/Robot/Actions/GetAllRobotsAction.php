<?php

namespace Domain\Robot\Actions;

use Domain\Robot\DTO\RobotIndexData;
use Domain\Robot\Models\Robot;
use Domain\Robot\Queries\RobotQueryBuilder;

class GetAllRobotsAction
{
    public function __invoke(RobotIndexData $data): RobotQueryBuilder
    {
        return Robot::query()
            ->checkAccess()
            ->when($data->search, fn (RobotQueryBuilder $query) => $query->search($data->search))
            ->when($data->zone_id, fn (RobotQueryBuilder $query) => $query->zoneId($data->zone_id))
            ->when($data->statuses, fn (RobotQueryBuilder $query) => $query->whereIn('status', $data->statuses))
            ->with('zone:id,name')
            ->latest()
            ;
    }
}
