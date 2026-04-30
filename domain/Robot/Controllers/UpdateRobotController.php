<?php

namespace Domain\Robot\Controllers;

use App\Http\Controllers\Controller;
use Domain\Robot\Actions\UpdateRobotAction;
use Domain\Robot\DTO\RobotData;
use Domain\Robot\Models\Robot;
use Domain\Robot\Requests\UpdateRobotRequest;
use Illuminate\Http\RedirectResponse;

class UpdateRobotController extends Controller
{
    public function __construct(
        private readonly UpdateRobotAction $updateRobot,
    ) {
    }

    public function __invoke(UpdateRobotRequest $request, Robot $robot): RedirectResponse
    {
        $data = RobotData::fromRequest($request);

        ($this->updateRobot)($robot, $data);

        return back();
    }
}
