<?php

namespace Domain\User\Actions;

use Domain\User\DTO\UserData;
use Domain\User\Models\User;
use Illuminate\Support\Facades\Hash;

class CreateUserAction
{
    public function __invoke(UserData $data): User
    {
        return User::create([
            'name' => $data->name,
            'email' => $data->email,
            'role' => $data->role,
            'password' => Hash::make($data->password),
        ]);
    }
}
