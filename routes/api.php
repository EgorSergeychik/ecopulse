<?php

use Domain\Telemetry\Controllers\StoreTelemetryController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->post('/telemetry', StoreTelemetryController::class);
