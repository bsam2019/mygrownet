<?php

namespace App\Domain\Core\Services;

class CacheKeyHelper
{
    public function __construct(
        private PlatformContextResolver $contextResolver,
    ) {}

    public function prefixed(string $key, ?int $organizationId = null): string
    {
        $orgId = $organizationId ?? $this->resolveOrganizationId();

        if ($orgId) {
            return "org:{$orgId}:{$key}";
        }

        return "global:{$key}";
    }

    public function forModule(string $module, string $key, ?int $organizationId = null): string
    {
        $orgPrefix = $organizationId ?? $this->resolveOrganizationId();

        if ($orgPrefix) {
            return "org:{$orgPrefix}:{$module}:{$key}";
        }

        return "global:{$module}:{$key}";
    }

    private function resolveOrganizationId(): ?int
    {
        $context = $this->contextResolver->current();
        return $context && $context->organizationId ? (int) $context->organizationId : null;
    }
}
