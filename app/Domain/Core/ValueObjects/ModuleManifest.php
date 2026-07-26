<?php

namespace App\Domain\Core\ValueObjects;

class ModuleManifest
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $version,
        public readonly string $category,
        public readonly string $type = 'tenant',
        public readonly string $description = '',
        public readonly string $minPlatformVersion = '1.0',
        public readonly string $maxPlatformVersion = '99.x',
        public readonly ?string $entrypoint = null,
        public readonly ?string $icon = null,
        public readonly bool $supportsSubdomain = false,
        public readonly bool $supportsWorkspaceLaunch = true,
        public readonly bool $requiresOrganization = true,
        public readonly array $permissions = [],
        public readonly array $capabilities = [],
        public readonly array $contracts = [],
        public readonly array $events = [],
        public readonly array $listens = [],
        public readonly array $requiredCapabilities = [],
        public readonly array $optionalCapabilities = [],
        public readonly array $settings = [],
        public readonly array $healthChecks = [],
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'version' => $this->version,
            'category' => $this->category,
            'type' => $this->type,
            'description' => $this->description,
            'min_platform_version' => $this->minPlatformVersion,
            'max_platform_version' => $this->maxPlatformVersion,
            'entrypoint' => $this->entrypoint,
            'icon' => $this->icon,
            'supports_subdomain' => $this->supportsSubdomain,
            'supports_workspace_launch' => $this->supportsWorkspaceLaunch,
            'requires_organization' => $this->requiresOrganization,
            'permissions' => $this->permissions,
            'capabilities' => $this->capabilities,
            'contracts' => $this->contracts,
            'events' => $this->events,
            'listens' => $this->listens,
            'required_capabilities' => $this->requiredCapabilities,
            'optional_capabilities' => $this->optionalCapabilities,
            'settings' => $this->settings,
            'health_checks' => $this->healthChecks,
        ];
    }
}
