<?php

namespace Domain\User\Controllers;

use App\Http\Controllers\Controller;
use App\Support\Enums\Role;
use Domain\Role\Resources\SelectRoleResource;
use Domain\User\Actions\GetAllUsersAction;
use Domain\User\DTO\UserIndexData;
use Domain\User\Resources\UserListResource;
use Illuminate\Http\Request;

class GetAllUsersController extends Controller
{
    public function __construct(
        private readonly GetAllUsersAction $getAllUsers,
    ) {
    }

    public function __invoke(Request $request)
    {
        $data = UserIndexData::fromRequest($request);
        $users = ($this->getAllUsers)($data);

        return inertia('Users', [
            'users' => UserListResource::collection($users),
            'roles' => SelectRoleResource::collection(Role::cases()),
            'filters' => [
                'search' => $data->search,
            ],
        ]);
    }
}
