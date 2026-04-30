<?php

namespace Domain\Robot\Actions;

use Domain\Robot\DTO\RobotData;
use Domain\Robot\Models\Robot;

class UpdateRobotAction
{
    public function __invoke(Robot $robot, RobotData $data): Robot
    {
        $robot->update([
            'name' => $data->name,
            'mac_address' => $data->mac_address,
            'zone_id' => $data->zone_id,
        ]);

        return $robot;
    }
}
