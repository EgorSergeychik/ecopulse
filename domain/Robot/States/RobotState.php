<?php

namespace Domain\Robot\States;

use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;

abstract class RobotState extends State
{
    public static function config(): StateConfig
    {
        return parent::config()
            ->default(Offline::class)
            ->allowAllTransitions()
            ->ignoreSameState();
    }
}
