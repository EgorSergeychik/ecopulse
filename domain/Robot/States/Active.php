<?php

namespace Domain\Robot\States;

use Domain\Robot\Enums\RobotState as RobotStateEnum;

class Active extends RobotState
{
    public static $name = RobotStateEnum::ACTIVE->value;
}
