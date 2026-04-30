<?php

namespace Domain\Robot\Controllers;

use App\Http\Controllers\Controller;
use Domain\Robot\DTO\RobotData;
use Domain\Robot\DTO\RobotIndexData;
use Domain\Robot\Models\Robot;
use Domain\Robot\Requests\StoreRobotRequest;
use Domain\Robot\Requests\UpdateRobotRequest;
use Domain\Robot\Resources\RobotListResource;
use Domain\Zone\Models\Zone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RobotController extends Controller
{
    public function index(Request $request)
    {
        $data = RobotIndexData::fromRequest($request);

        $robots = Robot::query()
            ->checkAccess()
            ->when($data->search, fn ($query) => $query->search($data->search))
            ->with('zone')
            ->latest()
            ->paginate($data->limit)
            ->withQueryString();

        $zones = Zone::query()
            ->checkAccess()
            ->orderBy('name')
            ->get(['id', 'name']);

        return inertia('Robots', [
            'robots' => RobotListResource::collection($robots),
            'zones' => $zones,
            'filters' => [
                'search' => $data->search,
            ],
        ]);
    }

    public function store(StoreRobotRequest $request): RedirectResponse
    {
        $data = RobotData::fromRequest($request);

        $payload = [
            'name' => $data->name,
            'mac_address' => $data->mac_address,
            'zone_id' => $data->zone_id,
        ];

        Robot::create($payload);

        return back();
    }

    public function update(UpdateRobotRequest $request, Robot $robot): RedirectResponse
    {
        $data = RobotData::fromRequest($request);

        $robot->update([
            'name' => $data->name,
            'mac_address' => $data->mac_address,
            'zone_id' => $data->zone_id,
        ]);

        return back();
    }

    public function destroy(Robot $robot): RedirectResponse
    {
        $robot->delete();

        return back();
    }
}
