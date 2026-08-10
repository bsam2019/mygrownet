<?php

namespace App\Domain\GrowMusic;

use App\Domain\Core\Services\ModuleDiscovery;
use App\Domain\Core\ValueObjects\ModuleManifest;
use App\Domain\GrowMusic\Services\MusicCreatorService;
use Illuminate\Support\ServiceProvider;

class GrowMusicServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(MusicCreatorService::class);
    }

    public function boot(): void
    {
        $discovery = $this->app->make(ModuleDiscovery::class);
        $discovery->register(new ModuleManifest(
            id: 'growmusic',
            name: 'GrowMusic',
            version: '1.0.0',
            category: 'media',
            description: 'Music streaming platform with ZAMCO royalties and creator payouts',
            capabilities: ['music_streaming', 'royalty_tracking'],
            permissions: ['manage_tracks', 'manage_artists', 'view_royalties'],
            settings: [],
        ));
    }
}
