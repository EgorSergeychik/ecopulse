<?php

namespace Domain\Robot\Policies;

use App\Support\Enums\Permission;
use Domain\Robot\Models\Robot;
use Domain\User\Models\User;

class RobotPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission(Permission::ViewRobots);
    }

    public function view(User $user, Robot $robot): bool
    {
        return $user->hasPermission(Permission::ViewRobots)
            && $robot->checkAccess($user);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission(Permission::CreateRobots);
    }

    public function update(User $user, Robot $robot): bool
    {
        return $user->hasPermission(Permission::UpdateRobots)
            && $robot->checkAccess($user);
    }

    public function delete(User $user, Robot $robot): bool
    {
        return $user->hasPermission(Permission::DeleteRobots)
            && $robot->checkAccess($user);
    }
}
