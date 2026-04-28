<?php

namespace Domain\User\Policies;

use App\Support\Enums\Permission;
use Domain\User\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission(Permission::ViewUsers);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission(Permission::CreateUsers);
    }

    public function update(User $user, User $model): bool
    {
        return $user->hasPermission(Permission::UpdateUsers);
    }

    public function delete(User $user, User $model): bool
    {
        return $user->hasPermission(Permission::DeleteUsers)
            && $user->id !== $model->id;
    }
}
