<?php

namespace App\Domain\Core\Services;

use App\Domain\Core\ValueObjects\ModuleManifest;
use Illuminate\Support\Collection;

class ModuleDiscovery
{
    private Collection $manifests;

    public function __construct()
    {
        $this->manifests = collect();
    }

    public function register(ModuleManifest $manifest): void
    {
        $this->manifests->put($manifest->id, $manifest);
    }

    public function all(): array
    {
        return $this->manifests->values()->map(fn(ModuleManifest $m) => $m->toArray())->all();
    }

    public function allManifests(): array
    {
        return $this->manifests->values()->all();
    }

    public function find(string $id): ?ModuleManifest
    {
        return $this->manifests->get($id);
    }

    public function has(string $id): bool
    {
        return $this->manifests->has($id);
    }

    public function capabilities(string $id): array
    {
        $manifest = $this->find($id);
        return $manifest ? $manifest->capabilities : [];
    }

    public function findProviders(string $capability): array
    {
        return $this->manifests
            ->filter(fn(ModuleManifest $m) => in_array($capability, $m->capabilities, true))
            ->keys()
            ->all();
    }

    public function hasCapability(string $id, string $capability): bool
    {
        $manifest = $this->find($id);
        return $manifest && in_array($capability, $manifest->capabilities, true);
    }

    public function count(): int
    {
        return $this->manifests->count();
    }

    public function contracts(string $id): array
    {
        $manifest = $this->find($id);
        return $manifest ? $manifest->contracts : [];
    }

    public function events(string $id): array
    {
        $manifest = $this->find($id);
        return $manifest ? $manifest->events : [];
    }

    public function findByContract(string $contractClass): ?ModuleManifest
    {
        return $this->manifests->first(
            fn(ModuleManifest $m) => in_array($contractClass, $m->contracts, true)
        );
    }

    public function findByCapability(string $capability): ?ModuleManifest
    {
        return $this->manifests->first(
            fn(ModuleManifest $m) => in_array($capability, $m->capabilities, true)
        );
    }
}
