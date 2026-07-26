<?php

namespace MyGrowNet\Platform\Sdk\Integration;

use MyGrowNet\Platform\Sdk\Context\PlatformContext;

class IntegrationGuard
{
    public function __construct(
        private \App\Domain\Core\Services\IntegrationGuard $core,
    ) {}

    public function authorize(PlatformContext $context, string $contractClass): void
    {
        $this->core->authorize($context->toCore(), $contractClass);
    }

    public function requireFeatureEnabled(string $feature, PlatformContext $context): void
    {
        $this->core->requireFeatureEnabled($feature, $context->toCore());
    }

    public static function instance(): self
    {
        return new self(app(\App\Domain\Core\Services\IntegrationGuard::class));
    }
}
