<?php

namespace Domain\User\Actions;

use Domain\User\DTO\UserData;
use Domain\User\Models\User;
use Illuminate\Support\Facades\Hash;

class UpdateUserAction
{
    public function __invoke(User $user, UserData $data): User
    {
        $user->update([
            'name' => $data->name,
            'email' => $data->email,
            'role' => $data->role,
            'password' => $data->password ? Hash::make($data->password) : $user->password,
        ]);

        return $user;
    }
}
