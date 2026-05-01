<?php

namespace Domain\Incident\Policies;

use App\Support\Enums\Permission;
use Domain\Incident\Models\Incident;
use Domain\User\Models\User;

class IncidentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission(Permission::ViewIncidents);
    }

    public function view(User $user, Incident $incident): bool
    {
        return $user->hasPermission(Permission::ViewIncidents)
            && $incident->robot->checkAccess($user);
    }

    public function resolve(User $user, Incident $incident): bool
    {
        return $user->hasPermission(Permission::ResolveIncidents)
            && $incident->robot->checkAccess($user)
            && !$incident->resolved_at;
    }
}
