<?php

namespace Domain\User\Controllers;

use App\Http\Controllers\Controller;
use Domain\User\Actions\CreateUserAction;
use Domain\User\DTO\UserData;
use Domain\User\Requests\StoreUserRequest;
use Illuminate\Http\RedirectResponse;

class CreateUserController extends Controller
{
    public function __construct(
        private readonly CreateUserAction $createUser,
    ) {
    }

    public function __invoke(StoreUserRequest $request): RedirectResponse
    {
        $data = UserData::fromRequest($request);

        ($this->createUser)($data);

        return back();
    }
}
