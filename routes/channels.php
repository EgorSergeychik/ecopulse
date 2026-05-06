<?php

use Domain\User\Models\User;
use Domain\Zone\Models\Zone;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('zone.{zone}', function (User $user, Zone $zone) {
    return $user->can('view', $zone);
});
