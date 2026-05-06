<?php

namespace Domain\Webots\Controllers;

use App\Http\Controllers\Controller;
use Domain\Zone\Models\Zone;
use Domain\Zone\Resources\ZoneListResource;

class WebotsIndexController extends Controller
{
    public function __invoke()
    {
        $zones = Zone::query()
            ->checkAccess()
            ->with(['media', 'users:id'])
            ->orderBy('name')
            ->get();

        return inertia('Webots', [
            'zones' => ZoneListResource::collection($zones),
        ]);
    }
}
