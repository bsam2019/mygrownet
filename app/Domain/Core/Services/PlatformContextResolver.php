<?php

namespace App\Domain\Core\Services;

use App\Domain\Core\ValueObjects\PlatformContext;
use Illuminate\Support\Str;

class PlatformContextResolver
{
    private ?PlatformContext $resolved = null;

    public function resolve(): PlatformContext
    {
        return $this->resolved ??= $this->resolveFromContainer();
    }

    public function current(): ?PlatformContext
    {
        return $this->resolved ?? app()->resolved(PlatformContext::class)
            ? app(PlatformContext::class)
            : null;
    }

    public function setContext(PlatformContext $context): void
    {
        $this->resolved = $context;
        app()->instance(PlatformContext::class, $context);
    }

    public function fallback(): PlatformContext
    {
        return PlatformContext::make(
            userId: '',
            organizationId: '',
            applicationId: '',
        );
    }

    public function forJob(string $userId = '', string $organizationId = '', string $applicationId = ''): PlatformContext
    {
        $context = PlatformContext::make(
            userId: $userId,
            organizationId: $organizationId,
            applicationId: $applicationId,
            traceId: (string) Str::uuid(),
            requestId: 'cli:' . (string) Str::uuid(),
        );

        $this->setContext($context);
        return $context;
    }

    private function resolveFromContainer(): PlatformContext
    {
        if (app()->resolved(PlatformContext::class)) {
            return app(PlatformContext::class);
        }

        return $this->fallback();
    }
}
