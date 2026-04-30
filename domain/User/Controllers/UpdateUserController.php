<?php

namespace Domain\User\Controllers;

use App\Http\Controllers\Controller;
use Domain\User\Actions\UpdateUserAction;
use Domain\User\DTO\UserData;
use Domain\User\Models\User;
use Domain\User\Requests\UpdateUserRequest;
use Illuminate\Http\RedirectResponse;

class UpdateUserController extends Controller
{
    public function __construct(
        private readonly UpdateUserAction $updateUser,
    ) {
    }

    public function __invoke(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $data = UserData::fromRequest($request);

        ($this->updateUser)($user, $data);

        return back();
    }
}
