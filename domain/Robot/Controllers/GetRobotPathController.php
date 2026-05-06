<?php

namespace Domain\Robot\Controllers;

use App\Http\Controllers\Controller;
use Domain\Robot\Models\Robot;
use Illuminate\Http\JsonResponse;

class GetRobotPathController extends Controller
{
    public function __invoke(Robot $robot): JsonResponse
    {
        $points = $robot->telemetryLogs()
            ->select(['lat', 'lng', 'recorded_at'])
            ->latest('recorded_at')
            ->limit(config('app.robots.path_length'))
            ->get()
            ->reverse()
            ->values();

        return response()->json($points);
    }
}
