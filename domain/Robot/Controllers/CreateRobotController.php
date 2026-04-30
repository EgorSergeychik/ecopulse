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

        ($this->createRobot)($data);

        return back();
    }
}
