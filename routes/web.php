<?php

use Domain\Robot\Controllers\CreateRobotController;
use Domain\Robot\Controllers\DeleteRobotController;
use Domain\Robot\Controllers\GetAllRobotsController;
use Domain\Robot\Controllers\UpdateRobotController;
use Domain\Robot\Models\Robot;
use Domain\User\Controllers\CreateUserController;
use Domain\User\Controllers\DeleteUserController;
use Domain\User\Controllers\GetAllUsersController;
use Domain\User\Controllers\UpdateUserController;
use Domain\User\Models\User;
use Domain\Zone\Controllers\CreateZoneController;
use Domain\Zone\Controllers\DeleteZoneController;
use Domain\Zone\Controllers\GetAllZonesController;
use Domain\Zone\Controllers\UpdateZoneController;
use Domain\Zone\Models\Zone;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
});

Route::group(['prefix' => 'users', 'middleware' => ['auth', 'verified']], function () {
    Route::get('/', GetAllUsersController::class)
        ->can('viewAny', User::class)->name('users.index');
    Route::post('/', CreateUserController::class)
        ->can('create', User::class)->name('users.store');
    Route::put('/{user}', UpdateUserController::class)
        ->can('update', 'user')->name('users.update');
    Route::delete('/{user}', DeleteUserController::class)
        ->can('delete', 'user')->name('users.destroy');
});

Route::group(['prefix' => 'zones', 'middleware' => ['auth', 'verified']], function () {
    Route::get('/', GetAllZonesController::class)
        ->can('viewAny', Zone::class)->name('zones.index');
    Route::post('/', CreateZoneController::class)
        ->can('create', Zone::class)->name('zones.store');
    Route::put('/{zone}', UpdateZoneController::class)
        ->can('update', 'zone')->name('zones.update');
    Route::delete('/{zone}', DeleteZoneController::class)
        ->can('delete', 'zone')->name('zones.destroy');
});

Route::group(['prefix' => 'robots', 'middleware' => ['auth', 'verified']], function () {
    Route::get('/', GetAllRobotsController::class)
        ->can('viewAny', Robot::class)->name('robots.index');
    Route::post('/', CreateRobotController::class)
        ->can('create', Robot::class)->name('robots.store');
    Route::put('/{robot}', UpdateRobotController::class)
        ->can('update', 'robot')->name('robots.update');
    Route::delete('/{robot}', DeleteRobotController::class)
        ->can('delete', 'robot')->name('robots.destroy');
});

require __DIR__.'/settings.php';
