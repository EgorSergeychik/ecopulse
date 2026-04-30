<?php

namespace Domain\Zone\Controllers;

use App\Http\Controllers\Controller;
use Domain\Zone\Actions\DeleteZoneAction;
use Domain\Zone\Models\Zone;
use Illuminate\Http\RedirectResponse;

class DeleteZoneController extends Controller
{
    public function __construct(
        private readonly DeleteZoneAction $deleteZone,
    ) {
    }

    public function __invoke(Zone $zone): RedirectResponse
    {
        ($this->deleteZone)($zone);

        return back();
    }
}
