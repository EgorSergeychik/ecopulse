<?php

namespace Domain\User\Controllers;

use App\Http\Controllers\Controller;
use App\Support\Enums\Role;
use Domain\Role\Resources\SelectRoleResource;
use Domain\User\DTO\UserData;
use Domain\User\DTO\UserIndexData;
use Domain\User\Models\User;
use Domain\User\Requests\StoreUserRequest;
use Domain\User\Requests\UpdateUserRequest;
use Domain\User\Resources\UserListResource;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $data = UserIndexData::fromRequest($request);

        $users = User::query()
            ->when($data->search, fn ($query) => $query->search($data->search))
            ->latest()
            ->paginate($data->limit)
            ->withQueryString();

        return inertia('Users', [
            'users' => UserListResource::collection($users),
            'roles' => SelectRoleResource::collection(Role::cases()),
            'filters' => [
                'search' => $data->search,
            ],
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
        $user->delete();

        return back();
    }
}
