<?php

namespace App\Domain\Workspace\Services;

use App\Domain\Core\Models\Domain;
use App\Domain\Workspace\Exceptions\DomainNotFoundException;
use App\Domain\Workspace\ValueObjects\DomainResolution;

class DomainResolverService
{
    public function resolve(string $host): DomainResolution
    {
        $domain = Domain::where('domain', $host)
            ->where('is_active', true)
            ->first();

        if (!$domain) {
            throw new DomainNotFoundException($host);
        }

        return new DomainResolution(
            type: $domain->type,
            application: $domain->application,
            organization: $domain->organization,
            route: $domain->route_path,
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
