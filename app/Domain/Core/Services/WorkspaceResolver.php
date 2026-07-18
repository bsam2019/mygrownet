<?php

namespace App\Domain\Core\Services;

use App\Domain\Core\Models\Application;
use App\Domain\Core\Models\CustomDomain;

class WorkspaceResolver
{
    public function resolveFromSubdomain(string $host): ?ResolvedWorkspace
    {
        if (!str_contains($host, '.mygrownet.com')) {
            return null;
        }

        $subdomain = explode('.', $host)[0];

        // Resolve subdomain → application slug via config/platform.php
        $apps = config('platform.applications', []);
        $slug = null;

        foreach ($apps as $key => $app) {
            if (($app['domain_slug'] ?? $key) === $subdomain) {
                $slug = $key;
                break;
            }
        }

        if (!$slug) {
            return null;
        }

        // Try database first (if migrated), fall back to config-only resolution
        $app = Application::where('slug', $slug)->where('is_active', true)->first();

        return new ResolvedWorkspace(
            type: 'application',
            application: $app,
        );
    }

    public function resolveFromCustomDomain(string $host): ?ResolvedWorkspace
    {
        $domain = CustomDomain::where('domain', $host)
            ->whereIn('status', ['active', 'verified'])
            ->first();

        if (!$domain) {
            return null;
        }

        return new ResolvedWorkspace(
            type: 'custom',
            domain: $domain,
            application: $domain->owner_type === Application::class
                ? $domain->owner
                : null,
        );
    }

    public function resolve(string $host): ?ResolvedWorkspace
    {
        return $this->resolveFromCustomDomain($host)
            ?? $this->resolveFromSubdomain($host);
    }
}
