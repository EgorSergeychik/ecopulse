<?php

namespace Domain\Robot\Controllers;

use App\Http\Controllers\Controller;
use Domain\Robot\Actions\CreateRobotAction;
use Domain\Robot\DTO\RobotData;
use Domain\Robot\Requests\StoreRobotRequest;
use Illuminate\Http\RedirectResponse;

class CreateRobotController extends Controller
{
    public function __construct(
        private readonly CreateRobotAction $createRobot,
    ) {
    }

    public function __invoke(StoreRobotRequest $request): RedirectResponse
    {
        $data = RobotData::fromRequest($request);

        $robot = ($this->createRobot)($data);
        $token = $robot->issueAccessToken();

        return back()->with('robot_token', [
            'robot_id' => $robot->id,
            'robot_name' => $robot->name,
            'token' => $token->plainTextToken,
        ]);
    }
}
