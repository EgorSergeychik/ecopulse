<?php

namespace Domain\Robot\Actions;

use Domain\Robot\Enums\RobotState as RobotStateEnum;
use Domain\Robot\Models\Robot;
use Domain\Robot\States\Maintenance;
use Domain\Robot\States\Offline;
use Illuminate\Validation\ValidationException;
use Spatie\ModelStates\Exceptions\CouldNotPerformTransition;

class TransitionRobotAction
{
    public function __invoke(Robot $robot, RobotStateEnum $state): Robot
    {
        try {
            $robot->status->transitionTo($this->resolveStateClass($state));
        } catch (CouldNotPerformTransition $exception) {
            throw ValidationException::withMessages([
                'state' => __('misc.robots.errors.transition_not_allowed'),
            ]);
        }

        return $robot->refresh();
    }

    private function resolveStateClass(RobotStateEnum $state): string
    {
        return match ($state) {
            RobotStateEnum::MAINTENANCE => Maintenance::class,
            RobotStateEnum::OFFLINE => Offline::class,
            default => throw ValidationException::withMessages([
                'state' => __('misc.robots.errors.only_maintenance_and_offline_allowed'),
            ]),
        };
    }
}
