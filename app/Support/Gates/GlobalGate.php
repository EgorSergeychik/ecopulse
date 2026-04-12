<?php

namespace App\Support\Gates;

use Domain\User\Models\User;

class GlobalGate
{
    public function viewDashboard(User $user): bool
    {
        return $user->isAdmin();
    }

    public function manageUsers(User $user): bool
    {
        return $user->isAdmin();
    }
}
