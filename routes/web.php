<?php

use Domain\User\Models\User;
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
    Route::get('/', [\Domain\User\Controllers\UserController::class, 'index'])
        ->can('viewAny', User::class)->name('users.index');
    Route::post('/', [\Domain\User\Controllers\UserController::class, 'store'])
        ->can('create', User::class)->name('users.store');
    Route::put('/{user}', [\Domain\User\Controllers\UserController::class, 'update'])
        ->can('update', 'user')->name('users.update');
    Route::delete('/{user}', [\Domain\User\Controllers\UserController::class, 'destroy'])
        ->can('delete', 'user')->name('users.destroy');
});

Route::group(['prefix' => 'zones', 'middleware' => ['auth', 'verified']], function () {
    Route::get('/', [\Domain\Zone\Controllers\ZoneController::class, 'index'])
        ->can('viewAny', Zone::class)->name('zones.index');
    Route::post('/', [\Domain\Zone\Controllers\ZoneController::class, 'store'])
        ->can('create', Zone::class)->name('zones.store');
    Route::put('/{zone}', [\Domain\Zone\Controllers\ZoneController::class, 'update'])
        ->can('update', 'zone')->name('zones.update');
    Route::delete('/{zone}', [\Domain\Zone\Controllers\ZoneController::class, 'destroy'])
        ->can('delete', 'zone')->name('zones.destroy');
});

require __DIR__.'/settings.php';
