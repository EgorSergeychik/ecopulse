<?php

namespace Domain\Robot\Controllers;

use App\Http\Controllers\Controller;
use Domain\Robot\Actions\GetAllRobotsAction;
use Domain\Robot\DTO\RobotIndexData;
use Domain\Robot\Resources\RobotListResource;
use Domain\Zone\Models\Zone;
use Illuminate\Http\Request;

class GetAllRobotsController extends Controller
{
    public function __construct(
        private readonly GetAllRobotsAction $getAllRobots,
    ) {
    }

    public function __invoke(Request $request)
    {
        $data = RobotIndexData::fromRequest($request);

        $robots = $data->is_paginated
            ? ($this->getAllRobots)($data)->paginate($data->limit)->withQueryString()
            : ($this->getAllRobots)($data)->get();
        $zones = Zone::query()->checkAccess()->orderBy('name')->get(['id', 'name']);

        return inertia('Robots', [
            'robots' => RobotListResource::collection($robots),
            'zones' => $zones,
            'filters' => [
                'search' => $data->search,
            ],
        ]);
    }
}
