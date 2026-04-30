<?php

namespace Domain\Zone\Actions;

use Domain\Zone\Models\Zone;

class DeleteZoneAction
{
    public function __invoke(Zone $zone): void
    {
        $zone->delete();
    }
}
