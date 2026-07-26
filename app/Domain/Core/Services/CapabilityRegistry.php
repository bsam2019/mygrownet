<?php

namespace App\Domain\Core\Services;

use App\Domain\Core\ValueObjects\ModuleManifest;

class CapabilityRegistry
{
    public function __construct(
        private ModuleDiscovery $discovery,
    ) {}

    public function findProviders(string $capability): array
    {
        return $this->discovery->findProviders($capability);
    }

    public function findProvider(string $capability): ?ModuleManifest
    {
        return $this->discovery->findByCapability($capability);
    }

    public function hasCapability(string $moduleId, string $capability): bool
    {
        return $this->discovery->hasCapability($moduleId, $capability);
    }

    public function capabilities(string $moduleId): array
    {
        return $this->discovery->capabilities($moduleId);
    }

    public function allCapabilities(): array
    {
        $caps = [];
        foreach ($this->discovery->all() as $manifest) {
            foreach ($manifest['capabilities'] as $cap) {
                $caps[$cap][] = $manifest['id'];
            }
        }
        return $caps;
    }

    public function modulesWithCapability(string $capability): array
    {
        return $this->discovery->findProviders($capability);
    }

    public function count(): int
    {
        return $this->discovery->count();
    }
}
