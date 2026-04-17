<?php

namespace Domain\User\Controllers;

use App\Http\Controllers\Controller;
use App\Support\Enums\Role;
use Domain\Role\Resources\SelectRoleResource;
use Domain\User\DTO\UserData;
use Domain\User\Models\User;
use Domain\User\Requests\StoreUserRequest;
use Domain\User\Requests\UpdateUserRequest;
use Domain\User\Resources\UserListResource;
use App\Support\Enums\Permission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::query()
            ->latest()
            ->paginate($request->input('per_page', 10));

        return inertia('Users', [
            'users' => UserListResource::collection($users),
            'roles' => SelectRoleResource::collection(Role::cases()),
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $data = UserData::fromRequest($request);

        User::create([
            'name' => $data->name,
            'email' => $data->email,
            'role' => $data->role,
            'password' => Hash::make($data->password),
        ]);

        return back();
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $data = UserData::fromRequest($request);

        $user->update([
            'name' => $data->name,
            'email' => $data->email,
            'role' => $data->role,
            'password' => $data->password ? Hash::make($data->password) : $user->password,
        ]);

        return back();
    }

    public function destroy(User $user): RedirectResponse
    {
        Gate::authorize(Permission::DeleteUsers, $user);
        $user->delete();

        return back();
    }
}
