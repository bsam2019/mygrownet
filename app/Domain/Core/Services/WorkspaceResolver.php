<?php

namespace App\Domain\Core\Services;

use App\Domain\Core\Models\Application;
use App\Domain\Core\Models\CustomDomain;
use App\Domain\Core\Models\Domain;
use App\Domain\Core\Models\Organization;

class WorkspaceResolver
{
    public function resolveFromDomainsTable(string $host): ?ResolvedWorkspace
    {
        try {
            $domain = Domain::where('domain', $host)
                ->where('is_active', true)
                ->first();
        } catch (\Exception $e) {
            return null;
        }

        if (!$domain) {
            return null;
        }

        return new ResolvedWorkspace(
            type: $domain->type,
            application: $domain->application,
            organization: $domain->organization,
            source: 'domains_table',
        );
    }

    public function resolveFromAppSubdomain(string $host): ?ResolvedWorkspace
    {
        if (!str_contains($host, '.mygrownet.com')) {
            return null;
        }

        $subdomain = explode('.', $host)[0];

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

        $app = Application::where('slug', $slug)->where('is_active', true)->first();
        if (!$app) {
            return null;
        }

        return new ResolvedWorkspace(
            type: 'application',
            application: $app,
            shouldAutoLaunch: true,
            source: 'subdomain',
        );
    }

    public function resolveFromOrganizationSubdomain(string $host): ?ResolvedWorkspace
    {
        if (!str_contains($host, '.mygrownet.com')) {
            return null;
        }

        $subdomain = explode('.', $host)[0];

        $org = Organization::where('slug', $subdomain)
            ->where('status', 'active')
            ->first();

        if (!$org) {
            return null;
        }

        return new ResolvedWorkspace(
            type: 'organization',
            organization: $org,
            source: 'subdomain',
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
            type: $domain->owner_type === Organization::class ? 'organization' : 'custom',
            domain: $domain,
            application: $domain->owner_type === Application::class
                ? $domain->owner
                : null,
            organization: $domain->owner_type === Organization::class
                ? $domain->owner
                : null,
            shouldAutoLaunch: $domain->owner_type === Application::class,
            source: 'custom_domain',
        );
    }

    public function resolve(string $host): ?ResolvedWorkspace
    {
        return $this->resolveFromDomainsTable($host)
            ?? $this->resolveFromCustomDomain($host)
            ?? $this->resolveFromAppSubdomain($host)
            ?? $this->resolveFromOrganizationSubdomain($host);
    }
}
