<?php

use App\Http\Controllers\Api\Platform\ApplicationController;
use App\Http\Controllers\Api\Platform\MemberController;
use App\Http\Controllers\Api\Platform\OrganizationController;
use App\Http\Controllers\Platform\EventReplayController;
use App\Http\Controllers\Platform\HealthController;
use Illuminate\Support\Facades\Route;

Route::withoutMiddleware(['auth:sanctum'])->group(function () {
    Route::get('health', [HealthController::class, 'index']);
    Route::get('health/all', [HealthController::class, 'all']);
});

Route::middleware(['auth:sanctum', 'throttle:platform-api'])->prefix('api/v1')->group(function () {
    Route::apiResource('organizations', OrganizationController::class)->except(['edit', 'create']);
    Route::get('organizations/{organization}/members', [MemberController::class, 'index']);
    Route::post('organizations/{organization}/members', [MemberController::class, 'store']);
    Route::delete('organizations/{organization}/members/{user}', [MemberController::class, 'destroy']);
    Route::get('applications', [ApplicationController::class, 'index']);
    Route::get('applications/{slug}', [ApplicationController::class, 'show']);
});

Route::middleware(['auth:sanctum', 'auth:web'])->prefix('admin')->group(function () {
    Route::get('replay-events', [EventReplayController::class, 'index']);
    Route::get('replay-events/names', [EventReplayController::class, 'events']);
    Route::post('replay-events/replay', [EventReplayController::class, 'replay']);
});
