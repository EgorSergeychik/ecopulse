<?php

namespace Domain\Zone\Controllers;

use App\Http\Controllers\Controller;
use Domain\User\Models\User;
use Domain\Zone\DTO\ZoneData;
use Domain\Zone\DTO\ZoneIndexData;
use Domain\Zone\Models\Zone;
use Domain\Zone\Requests\StoreZoneRequest;
use Domain\Zone\Requests\UpdateZoneRequest;
use Domain\Zone\Resources\ZoneListResource;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ZoneController extends Controller
{
    public function index(Request $request)
    {
        $data = ZoneIndexData::fromRequest($request);

        $zones = Zone::query()
            ->checkAccess()
            ->when($data->search, fn ($query) => $query->search($data->search))
            ->with('users')
            ->paginate($data->limit)
            ->withQueryString();

        $users = User::query()
            ->operator()
            ->orderBy('name')
            ->get(['id', 'name']);

        return inertia('Zones', [
            'zones' => ZoneListResource::collection($zones),
            'users' => $users,
            'filters' => [
                'search' => $data->search,
            ],
        ]);
    }

    public function store(StoreZoneRequest $request): RedirectResponse
    {
        $data = ZoneData::fromRequest($request);

        $zone = Zone::create([
            'name' => $data->name,
            'center_lat' => $data->center_lat,
            'center_lng' => $data->center_lng,
            'default_zoom' => $data->default_zoom,
            'bounding_box' => $data->polygon,
        ]);

        $zone->users()->sync($data->user_ids);

        if ($request->hasFile('thumbnail')) {
            $zone->addMediaFromRequest('thumbnail')
                ->sanitizingFileName(fn(string $name) => preg_replace('/[^a-zA-Z0-9.\-_]/', '-', $name))
                ->toMediaCollection('thumbnail');
        }

        return back();
    }

    public function update(UpdateZoneRequest $request, Zone $zone): RedirectResponse
    {
        $data = ZoneData::fromRequest($request);

        $zone->update([
            'name' => $data->name,
            'center_lat' => $data->center_lat,
            'center_lng' => $data->center_lng,
            'default_zoom' => $data->default_zoom,
            'bounding_box' => $data->polygon,
        ]);

        $zone->users()->sync($data->user_ids);

        if ($request->hasFile('thumbnail')) {
            $zone->addMediaFromRequest('thumbnail')
                ->sanitizingFileName(fn(string $name) => preg_replace('/[^a-zA-Z0-9.\-_]/', '-', $name))
                ->toMediaCollection('thumbnail');
        }

        return back();
    }

    public function destroy(Zone $zone): RedirectResponse
    {
        $zone->delete();

        return back();
    }
}
