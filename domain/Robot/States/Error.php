<?php

namespace Domain\Robot\States;

use Domain\Robot\Enums\RobotState as RobotStateEnum;

class Error extends RobotState
{
    public static $name = RobotStateEnum::ERROR->value;
}
