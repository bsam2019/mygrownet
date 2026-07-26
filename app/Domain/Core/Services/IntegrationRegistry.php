<?php

namespace App\Domain\Core\Services;

use App\Domain\Core\Contracts\ProviderContract;
use App\Domain\Core\Exceptions\ConfigurationException;
use App\Domain\Core\Exceptions\NotFoundException;

class IntegrationRegistry
{
    private array $resolved = [];

    public function __construct(
        private ModuleDiscovery $discovery,
    ) {}

    public function resolve(string $contractClass): ProviderContract
    {
        if (isset($this->resolved[$contractClass])) {
            return $this->resolved[$contractClass];
        }

        if (!is_subclass_of($contractClass, ProviderContract::class)) {
            throw new ConfigurationException("{$contractClass} does not extend ProviderContract");
        }

        if (!app()->has($contractClass)) {
            $manifest = $this->discovery->findByContract($contractClass);

            if (!$manifest) {
                throw new NotFoundException("No provider registered for contract: {$contractClass}");
            }

            throw new ConfigurationException(
                "Contract {$contractClass} declared by {$manifest->id} but not bound in the container"
            );
        }

        $this->resolved[$contractClass] = app($contractClass);
        return $this->resolved[$contractClass];
    }

    public function resolveFor(string $capability): ProviderContract
    {
        $manifest = $this->discovery->findByCapability($capability);

        if (!$manifest) {
            throw new NotFoundException("No provider found for capability: {$capability}");
        }

        $contracts = $manifest->contracts;
        if (empty($contracts)) {
            throw new ConfigurationException("Manifest {$manifest->id} declares capability {$capability} but no contracts");
        }

        return $this->resolve($contracts[0]);
    }

    public function clearCache(): void
    {
        $this->resolved = [];
    }
}
