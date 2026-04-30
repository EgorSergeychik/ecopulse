<?php

namespace Domain\Zone\Controllers;

use App\Http\Controllers\Controller;
use Domain\Zone\Actions\UpdateZoneAction;
use Domain\Zone\DTO\ZoneData;
use Domain\Zone\Models\Zone;
use Domain\Zone\Requests\UpdateZoneRequest;
use Illuminate\Http\RedirectResponse;

class UpdateZoneController extends Controller
{
    public function __construct(
        private readonly UpdateZoneAction $updateZone,
    ) {
    }

    public function __invoke(UpdateZoneRequest $request, Zone $zone): RedirectResponse
    {
        $data = ZoneData::fromRequest($request);

        ($this->updateZone)($zone, $data, $request);

        return back();
    }
}
