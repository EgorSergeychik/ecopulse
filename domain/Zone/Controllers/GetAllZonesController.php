<?php

namespace Domain\Zone\Controllers;

use App\Http\Controllers\Controller;
use Domain\User\Models\User;
use Domain\Zone\Actions\GetAllZonesAction;
use Domain\Zone\DTO\ZoneIndexData;
use Domain\Zone\Resources\ZoneListResource;
use Illuminate\Http\Request;

class GetAllZonesController extends Controller
{
    public function __construct(
        private readonly GetAllZonesAction $getAllZones,
    ) {
    }

    public function __invoke(Request $request)
    {
        $data = ZoneIndexData::fromRequest($request);

        $zones = ($this->getAllZones)($data);
        $users = User::query()->operator()->orderBy('name')->get(['id', 'name']);

        return inertia('Zones', [
            'zones' => ZoneListResource::collection($zones),
            'users' => $users,
            'filters' => [
                'search' => $data->search,
            ],
        ]);
    }
}
