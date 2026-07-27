<?php

namespace App\Domain\Core\Services;

use App\Domain\Core\Contracts\DimensionProvider;

class DimensionResolver
{
    private array $providers = [];

    public function register(DimensionProvider $provider): void
    {
        $this->providers[] = $provider;
    }

    public function allDimensions(): array
    {
        $dimensions = [];

        foreach ($this->providers as $provider) {
            $dimensions[$provider->capability()] = $provider->getDimensions();
        }

        return $dimensions;
    }

    public function resolveLabels(array $dimensionIds): array
    {
        $result = [];

        foreach ($this->providers as $provider) {
            $labels = $provider->resolveLabels($dimensionIds);
            $result[$provider->capability()] = $labels;
        }

        return $result;
    }
}
