<?php

namespace Domain\Webots\Controllers;

use App\Http\Controllers\Controller;
use Domain\Robot\Models\Robot;
use Domain\Webots\Actions\BuildRoadRouteAction;
use Domain\Webots\Actions\GenerateHotspotsAction;
use Domain\Webots\Services\RobotControllerTemplate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Throwable;

class GenerateRobotControllerController extends Controller
{
    public function __construct(
        private readonly BuildRoadRouteAction $buildRoadRoute,
        private readonly GenerateHotspotsAction $generateHotspots,
    ) {
    }

    public function __invoke(Robot $robot): Response|JsonResponse
    {
        [$south, $west, $north, $east] = $this->bbox($robot);

        try {
            $waypoints = ($this->buildRoadRoute)($south, $west, $north, $east);
        } catch (Throwable) {
            return response()->json(['message' => __('misc.robots.controller_unavailable')], 503);
        }

        $hotspots = ($this->generateHotspots)($robot->zone_id ?? 0, $south, $west, $north, $east);

        $refLat = ($south + $north) / 2;
        $refLng = ($west + $east) / 2;

        $script = (new RobotControllerTemplate)->generate(
            waypoints: $waypoints,
            hotspots:  $hotspots,
            refLat:    $refLat,
            refLng:    $refLng,
            apiUrl:    rtrim(config('app.url'), '/'),
        );

        return response($script, 200, [
            'Content-Type'        => 'text/x-python',
            'Content-Disposition' => 'attachment; filename="robot_controller.py"',
        ]);
    }

    private function bbox(Robot $robot): array
    {
        $bb = $robot->zone?->bounding_box;

        if (is_array($bb) && isset($bb['south'], $bb['west'], $bb['north'], $bb['east'])) {
            return [(float) $bb['south'], (float) $bb['west'], (float) $bb['north'], (float) $bb['east']];
        }

        $lat = (float) ($robot->zone?->center_lat ?? 50.45);
        $lng = (float) ($robot->zone?->center_lng ?? 30.52);
        $delta = 0.005;

        return [$lat - $delta, $lng - $delta, $lat + $delta, $lng + $delta];
    }
}
