<?php

namespace App\Support\Gates;

use Domain\User\Models\User;

class GlobalGate
{
    /*
     * Dashboard
     */

    public function viewDashboard(User $admin): bool
    {
        return $admin->isAdmin();
    }

    /*
     * Users
     */

    public function viewUsers(User $admin): bool
    {
        return $admin->isAdmin();
    }

    public function manageUsers(User $admin): bool
    {
        return false; // Only superadmins can manage users
    }

    public function deleteUsers(User $admin, ?User $user = null): bool
    {
        if ($user === null) {
            return $admin->isSuperAdmin();
        }

        return $admin->isSuperAdmin() && $admin->id !== $user->id;
    }

    /*
     * Roles
     */

    public function viewRoles(User $user): bool
    {
        return false;
    }

    /*
     * Zones
     */

    public function viewZones(User $admin): bool
    {
        return $admin->isAdmin();
    }

    public function manageZones(User $admin): bool
    {
        return $admin->isAdmin();
    }

    public function deleteZones(User $admin): bool
    {
        return $admin->isAdmin();
    }
}
