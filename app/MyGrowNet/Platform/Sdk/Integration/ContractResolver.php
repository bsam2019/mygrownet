<?php

namespace MyGrowNet\Platform\Sdk\Integration;

use App\Domain\Core\Contracts\ProviderContract;

class ContractResolver
{
    public function __construct(
        private \App\Domain\Core\Services\IntegrationRegistry $registry,
    ) {}

    public function resolve(string $contractClass): ProviderContract
    {
        return $this->registry->resolve($contractClass);
    }

    public function hasContract(string $contractClass): bool
    {
        return $this->registry->has($contractClass);
    }

    public static function instance(): self
    {
        return new self(app(\App\Domain\Core\Services\IntegrationRegistry::class));
    }
}
