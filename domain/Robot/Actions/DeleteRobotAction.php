<?php

namespace Domain\Robot\Actions;

use Domain\Robot\Models\Robot;

class DeleteRobotAction
{
    public function __invoke(Robot $robot): void
    {
        $robot->delete();
    }
}
