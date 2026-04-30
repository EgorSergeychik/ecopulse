<?php

namespace Domain\Robot\Controllers;

use App\Http\Controllers\Controller;
use Domain\Robot\Actions\DeleteRobotAction;
use Domain\Robot\Models\Robot;
use Illuminate\Http\RedirectResponse;

class DeleteRobotController extends Controller
{
    public function __construct(
        private readonly DeleteRobotAction $deleteRobot,
    ) {
    }

    public function __invoke(Robot $robot): RedirectResponse
    {
        ($this->deleteRobot)($robot);

        return back();
    }
}
