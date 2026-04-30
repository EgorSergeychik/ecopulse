<?php

namespace Domain\Robot\States;

use Domain\Robot\Enums\RobotState as RobotStateEnum;

class Offline extends RobotState
{
    public static $name = RobotStateEnum::OFFLINE->value;
}
