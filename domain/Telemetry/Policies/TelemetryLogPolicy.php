<?php

namespace Domain\Telemetry\Policies;

use App\Support\Enums\Permission;
use Domain\Telemetry\Models\TelemetryLog;
use Domain\User\Models\User;

class TelemetryLogPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission(Permission::ViewTelemetryLogs);
    }

    public function view(User $user, TelemetryLog $telemetryLog): bool
    {
        return $user->hasPermission(Permission::ViewTelemetryLogs)
            && $telemetryLog->robot->checkAccess($user);
    }
}
