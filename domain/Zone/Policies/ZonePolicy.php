<?php

namespace Domain\Zone\Policies;

use App\Support\Enums\Permission;
use Domain\User\Models\User;
use Domain\Zone\Models\Zone;

class ZonePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission(Permission::ViewZones);
    }

    public function view(User $user, Zone $zone): bool
    {
        return $user->hasPermission(Permission::ViewZones)
            && $zone->checkAccess($user);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission(Permission::CreateZones);
    }

    public function update(User $user, Zone $zone): bool
    {
        return $user->hasPermission(Permission::UpdateZones)
            && $zone->checkAccess($user);
    }

    public function delete(User $user, Zone $zone): bool
    {
        return $user->hasPermission(Permission::DeleteZones)
            && $zone->checkAccess($user);
    }
}
