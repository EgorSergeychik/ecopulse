<?php

namespace App\Support\Gates;

use Domain\User\Models\User;

class GlobalGate
{
    /*
     * Dashboard
     */

    public function viewDashboard(User $user): bool
    {
        return $user->isAdmin();
    }

    /*
     * Users
     */

    public function viewUsers(User $user): bool
    {
        return $user->isAdmin();
    }

    public function manageUsers(User $user): bool
    {
        return false; // Only superadmins can manage users
    }
}
