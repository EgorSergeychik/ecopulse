<?php

namespace Domain\User\Controllers;

use App\Http\Controllers\Controller;
use Domain\User\Actions\DeleteUserAction;
use Domain\User\Models\User;
use Illuminate\Http\RedirectResponse;

class DeleteUserController extends Controller
{
    public function __construct(
        private readonly DeleteUserAction $deleteUser,
    ) {
    }

    public function __invoke(User $user): RedirectResponse
    {
        ($this->deleteUser)($user);

        return back();
    }
}
