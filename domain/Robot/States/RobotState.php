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

            ->allowTransition(Offline::class, Active::class)

            ->allowTransition(Active::class, Error::class)
            ->allowTransition(Active::class, Maintenance::class)

            ->allowTransition(Error::class, Maintenance::class)

            ->allowTransition(Maintenance::class, Offline::class)
            ;
    }
}
