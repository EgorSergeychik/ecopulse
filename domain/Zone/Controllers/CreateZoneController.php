<?php

namespace Domain\Zone\Controllers;

use App\Http\Controllers\Controller;
use Domain\Zone\Actions\CreateZoneAction;
use Domain\Zone\DTO\ZoneData;
use Domain\Zone\Requests\StoreZoneRequest;
use Illuminate\Http\RedirectResponse;

class CreateZoneController extends Controller
{
    public function __construct(
        private readonly CreateZoneAction $createZone,
    ) {
    }

    public function __invoke(StoreZoneRequest $request): RedirectResponse
    {
        $data = ZoneData::fromRequest($request);

        ($this->createZone)($data, $request);

        return back();
    }
}
