<?php

use Domain\Incident\Controllers\GetAllIncidentsController;
use Domain\Dashboard\Controllers\GetDashboardController;
use Domain\Webots\Controllers\DownloadOsmController;
use Domain\Webots\Controllers\DownloadWbtController;
use Domain\Webots\Controllers\WebotsIndexController;
use Domain\Incident\Controllers\ResolveIncidentController;
use Domain\Incident\Models\Incident;
use Domain\Robot\Controllers\CreateRobotController;
use Domain\Robot\Controllers\DeleteRobotController;
use Domain\Robot\Controllers\GetAllRobotsController;
use Domain\Robot\Controllers\GetRobotPathController;
use Domain\Robot\Controllers\RegenerateRobotTokenController;
use Domain\Robot\Controllers\TransitionRobotController;
use Domain\Robot\Controllers\UpdateRobotController;
use Domain\Robot\Models\Robot;
use Domain\Telemetry\Controllers\GetAllTelemetryLogsController;
use Domain\Telemetry\Models\TelemetryLog;
use Domain\User\Controllers\CreateUserController;
use Domain\User\Controllers\DeleteUserController;
use Domain\User\Controllers\GetAllUsersController;
use Domain\User\Controllers\UpdateUserController;
use Domain\User\Models\User;
use Domain\Zone\Controllers\CreateZoneController;
use Domain\Zone\Controllers\DeleteZoneController;
use Domain\Zone\Controllers\GetAllZonesController;
use Domain\Zone\Controllers\GetZoneController;
use Domain\Zone\Controllers\UpdateZoneController;
use Domain\Zone\Models\Zone;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', fn () => Inertia::render('Welcome', [
    'canRegister' => Features::enabled(Features::registration()) && !User::exists(),
]))->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', GetDashboardController::class)->name('dashboard');
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
    Route::get('/{zone}', GetZoneController::class)
        ->can('view', 'zone')->name('zones.show');
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
    Route::get('/{robot}/path', GetRobotPathController::class)
        ->can('view', 'robot')->name('robots.path');
    Route::post('/', CreateRobotController::class)
        ->can('create', Robot::class)->name('robots.store');
    Route::put('/{robot}', UpdateRobotController::class)
        ->can('update', 'robot')->name('robots.update');
    Route::post('/{robot}/token/regenerate', RegenerateRobotTokenController::class)
        ->can('update', 'robot')->name('robots.token.regenerate');
    Route::post('/{robot}/to/{state}', TransitionRobotController::class)
        ->can('transition', 'robot')->name('robots.transition');
    Route::delete('/{robot}', DeleteRobotController::class)
        ->can('delete', 'robot')->name('robots.destroy');
});

Route::group(['prefix' => 'telemetry-logs', 'middleware' => ['auth', 'verified']], function () {
    Route::get('/', GetAllTelemetryLogsController::class)
        ->can('viewAny', TelemetryLog::class)->name('telemetry-logs.index');
});

Route::group(['prefix' => 'incidents', 'middleware' => ['auth', 'verified']], function () {
    Route::get('/', GetAllIncidentsController::class)
        ->can('viewAny', Incident::class)->name('incidents.index');
    Route::post('/{incident}/resolve', ResolveIncidentController::class)
        ->can('resolve', 'incident')->name('incidents.resolve');
});

Route::group(['prefix' => 'webots', 'middleware' => ['auth', 'verified']], function () {
    Route::get('/', WebotsIndexController::class)
        ->can('viewAny', Zone::class)->name('webots.index');
    Route::get('/download/wbt', DownloadWbtController::class)
        ->can('viewAny', Zone::class)->name('webots.download-wbt');
    Route::get('/download/osm', DownloadOsmController::class)
        ->can('viewAny', Zone::class)->name('webots.download');
});

require __DIR__.'/settings.php';
