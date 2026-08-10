<?php

use App\Domain\GrowMusic\Presentation\Http\Controllers\Web\GrowMusicController;
use Illuminate\Support\Facades\Route;

Route::domain('growmusic.' . config('platform.central_domain', 'mygrownet.com'))
    ->name('growmusic.')
    ->middleware(['web'])
    ->group(function () {
        Route::get('/', [GrowMusicController::class, 'index'])->name('home');
        Route::get('/player/{id}', [GrowMusicController::class, 'showPlayer'])->name('player.show');
        Route::post('/stream-log', [GrowMusicController::class, 'logStream'])->name('stream.log');
    });
