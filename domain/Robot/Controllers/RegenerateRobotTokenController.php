<?php

namespace Domain\Robot\Controllers;

use App\Http\Controllers\Controller;
use Domain\Robot\Models\Robot;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class RegenerateRobotTokenController extends Controller
{
    public function __invoke(Robot $robot): RedirectResponse
    {
        $token = $robot->regenerateAccessToken();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('misc.messages.robot_token_regenerated'),
        ]);

        return back()->with('robot_token', [
            'robot_id' => $robot->id,
            'robot_name' => $robot->name,
            'token' => $token->plainTextToken,
        ]);
    }
}
