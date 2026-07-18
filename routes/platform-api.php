<?php

use App\Http\Controllers\Api\Platform\ApplicationController;
use App\Http\Controllers\Api\Platform\MemberController;
use App\Http\Controllers\Api\Platform\OrganizationController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'throttle:platform-api'])->prefix('api/v1')->group(function () {
    Route::apiResource('organizations', OrganizationController::class)->except(['edit', 'create']);
    Route::get('organizations/{organization}/members', [MemberController::class, 'index']);
    Route::post('organizations/{organization}/members', [MemberController::class, 'store']);
    Route::delete('organizations/{organization}/members/{user}', [MemberController::class, 'destroy']);
    Route::get('applications', [ApplicationController::class, 'index']);
    Route::get('applications/{slug}', [ApplicationController::class, 'show']);
});
