<?php

namespace Domain\Robot\Actions;

use Domain\Robot\DTO\RobotData;
use Domain\Robot\Models\Robot;

class CreateRobotAction
{
    public function __invoke(RobotData $data): Robot
    {
        return Robot::create([
            'name' => $data->name,
            'mac_address' => $data->mac_address,
            'zone_id' => $data->zone_id,
        ]);
    }
}
