<?php

use Domain\Robot\Controllers\RobotController;
use Domain\Robot\Models\Robot;
use Domain\User\Controllers\UserController;
use Domain\User\Models\User;
use Domain\Zone\Controllers\ZoneController;
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
    Route::get('/', [UserController::class, 'index'])
        ->can('viewAny', User::class)->name('users.index');
    Route::post('/', [UserController::class, 'store'])
        ->can('create', User::class)->name('users.store');
    Route::put('/{user}', [UserController::class, 'update'])
        ->can('update', 'user')->name('users.update');
    Route::delete('/{user}', [UserController::class, 'destroy'])
        ->can('delete', 'user')->name('users.destroy');
});

Route::group(['prefix' => 'zones', 'middleware' => ['auth', 'verified']], function () {
    Route::get('/', [ZoneController::class, 'index'])
        ->can('viewAny', Zone::class)->name('zones.index');
    Route::post('/', [ZoneController::class, 'store'])
        ->can('create', Zone::class)->name('zones.store');
    Route::put('/{zone}', [ZoneController::class, 'update'])
        ->can('update', 'zone')->name('zones.update');
    Route::delete('/{zone}', [ZoneController::class, 'destroy'])
        ->can('delete', 'zone')->name('zones.destroy');
});

Route::group(['prefix' => 'robots', 'middleware' => ['auth', 'verified']], function () {
    Route::get('/', [RobotController::class, 'index'])
        ->can('viewAny', Robot::class)->name('robots.index');
    Route::post('/', [RobotController::class, 'store'])
        ->can('create', Robot::class)->name('robots.store');
    Route::put('/{robot}', [RobotController::class, 'update'])
        ->can('update', 'robot')->name('robots.update');
    Route::delete('/{robot}', [RobotController::class, 'destroy'])
        ->can('delete', 'robot')->name('robots.destroy');
});

require __DIR__.'/settings.php';
