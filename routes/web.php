<?php

use App\Support\Enums\Permission;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
});

Route::group(['prefix' => 'users', 'middleware' => ['auth', 'verified']], function () {
    Route::get('/', [\Domain\User\Controllers\UserController::class, 'index'])
        ->can(Permission::ViewUsers)->name('users.index');
});

require __DIR__.'/settings.php';
