<?php

namespace Domain\User\Controllers;

use App\Http\Controllers\Controller;
use Domain\User\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::query()
            ->latest()
            ->paginate(10);

        return inertia('Users', [
            'users' => $users,
        ]);
    }
}
