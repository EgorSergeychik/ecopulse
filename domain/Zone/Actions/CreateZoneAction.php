<?php

namespace Domain\Zone\Actions;

use Domain\Zone\DTO\ZoneData;
use Domain\Zone\Models\Zone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

class CreateZoneAction
{
    public function __invoke(ZoneData $data, FormRequest $request): Zone
    {
        return DB::transaction(function () use ($data, $request) {
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
                    ->sanitizingFileName(fn (string $name) => preg_replace('/[^a-zA-Z0-9.\-_]/', '-', $name))
                    ->toMediaCollection('thumbnail');
            }

            return $zone;
        });
    }
}
