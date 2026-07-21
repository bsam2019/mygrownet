<?php

namespace App\Domain\Workspace\Services;

use App\Domain\Core\Services\WorkspaceResolver;
use App\Domain\Workspace\Exceptions\DomainNotFoundException;
use App\Domain\Workspace\ValueObjects\DomainResolution;

class DomainResolverService
{
    public function __construct(
        private WorkspaceResolver $workspaceResolver,
    ) {}

    public function resolve(string $host): DomainResolution
    {
        $resolved = $this->workspaceResolver->resolve($host);

        if (!$resolved) {
            throw new DomainNotFoundException($host);
        }

        return new DomainResolution(
            type: $resolved->type,
            application: $resolved->application,
            organization: $resolved->organization,
            route: $resolved->domain?->route_path ?? '/',
            shouldAutoLaunch: $resolved->shouldAutoLaunch,
            source: $resolved->source,
        );
    }

    public function resolveOrNull(string $host): ?DomainResolution
    {
        try {
            return $this->resolve($host);
        } catch (DomainNotFoundException) {
            return null;
        }
    }
}
