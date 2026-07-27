<?php

namespace App\Domain\Core\Contracts;

interface DimensionProvider extends ProviderContract
{
    public function getDimensions(): array;

    /**
     * Resolve dimension labels for a given set of dimension IDs.
     * Returns [dimensionName => [id => label, ...], ...].
     */
    public function resolveLabels(array $dimensionIds): array;
}
