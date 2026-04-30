<?php

namespace Domain\User\Actions;

use Domain\User\Models\User;

class DeleteUserAction
{
    public function __invoke(User $user): void
    {
        $user->delete();
    }
}
