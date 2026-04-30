<?php

namespace Domain\Robot\States;

use Domain\Robot\Enums\RobotState as RobotStateEnum;

class Maintenance extends RobotState
{
    public static $name = RobotStateEnum::MAINTENANCE->value;
}
