<?php

namespace App\Domain\GrowStream\Infrastructure\Providers;

use InvalidArgumentException;

class VideoProviderFactory
{
    public static function make(?string $provider = null): VideoProviderInterface
    {
        $provider = $provider ?? config('growstream.default_provider');

        return match ($provider) {
            'cloudflare' => app(CloudflareStreamProvider::class),
            'local' => throw new \Exception('Local provider not yet implemented'),
            default => throw new InvalidArgumentException("Provider {$provider} not supported")
        };
    }
}
