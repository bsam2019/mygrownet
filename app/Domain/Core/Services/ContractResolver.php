<?php

namespace App\Domain\Core\Services;

class ContractResolver
{
    public function __construct(
        private ModuleDiscovery $discovery,
    ) {}

    public function findProviderForContract(string $contractClass): ?string
    {
        $manifest = $this->discovery->findByContract($contractClass);
        return $manifest?->id;
    }

    public function findProviderForCapability(string $capability): ?string
    {
        $manifest = $this->discovery->findByCapability($capability);
        return $manifest?->id;
    }

    public function canResolve(string $contractClass): bool
    {
        if (!app()->has($contractClass)) {
            return false;
        }
        return $this->discovery->findByContract($contractClass) !== null;
    }

    public function availableContracts(): array
    {
        $contracts = [];
        foreach ($this->discovery->all() as $manifest) {
            foreach ($manifest->contracts as $contract) {
                $contracts[] = [
                    'contract' => $contract,
                    'provider' => $manifest->id,
                    'capabilities' => $manifest->capabilities,
                ];
            }
        }
        return $contracts;
    }
}
