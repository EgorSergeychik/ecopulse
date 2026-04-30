<?php

namespace Domain\Robot\Controllers;

use App\Http\Controllers\Controller;
use Domain\Robot\Actions\TransitionRobotAction;
use Domain\Robot\Enums\RobotState as RobotStateEnum;
use Domain\Robot\Models\Robot;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;

class TransitionRobotController extends Controller
{
    public function __construct(
        private readonly TransitionRobotAction $transitionRobot,
    ) {}

    public function __invoke(Robot $robot, string $state): RedirectResponse
    {
        $targetState = RobotStateEnum::tryFrom($state);

        if (! $targetState) {
            throw ValidationException::withMessages([
                'state' => __('misc.robots.errors.invalid_status'),
            ]);
        }

        ($this->transitionRobot)($robot, $targetState);

        return back();
    }
}
