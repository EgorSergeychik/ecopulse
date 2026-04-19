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
    Route::post('/', [\Domain\User\Controllers\UserController::class, 'store'])
        ->can(Permission::CreateUsers)->name('users.store');
    Route::put('/{user}', [\Domain\User\Controllers\UserController::class, 'update'])
        ->can(Permission::UpdateUsers)->name('users.update');
    Route::delete('/{user}', [\Domain\User\Controllers\UserController::class, 'destroy'])
        ->name('users.destroy');
});

Route::group(['prefix' => 'zones', 'middleware' => ['auth', 'verified']], function () {
    Route::get('/', [\Domain\Zone\Controllers\ZoneController::class, 'index'])
        ->can(Permission::ViewZones)->name('zones.index');
    Route::post('/', [\Domain\Zone\Controllers\ZoneController::class, 'store'])
        ->can(Permission::CreateZones)->name('zones.store');
    Route::put('/{zone}', [\Domain\Zone\Controllers\ZoneController::class, 'update'])
        ->can(Permission::UpdateZones)->name('zones.update');
    Route::delete('/{zone}', [\Domain\Zone\Controllers\ZoneController::class, 'destroy'])
        ->name('zones.destroy');
});

require __DIR__.'/settings.php';
