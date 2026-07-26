<?php

namespace App\Domain\Core\ValueObjects;

use Illuminate\Support\Str;

class PlatformContext
{
    public function __construct(
        public readonly string $traceId,
        public readonly string $requestId,
        public readonly string $userId,
        public readonly string $organizationId,
        public readonly string $applicationId,
        public readonly ?string $installationId = null,
        public readonly string $workspaceId = '',
        public readonly string $locale = 'en',
        public readonly string $timezone = 'UTC',
    ) {}

    public static function make(
        string $userId,
        string $organizationId,
        string $applicationId,
        ?string $installationId = null,
        ?string $traceId = null,
        ?string $requestId = null,
        string $workspaceId = '',
        string $locale = 'en',
        string $timezone = 'UTC',
    ): self {
        return new self(
            traceId: $traceId ?? (string) Str::uuid(),
            requestId: $requestId ?? (string) Str::uuid(),
            userId: $userId,
            organizationId: $organizationId,
            applicationId: $applicationId,
            installationId: $installationId,
            workspaceId: $workspaceId,
            locale: $locale,
            timezone: $timezone,
        );
    }

    public function withInstallation(string $installationId): self
    {
        return new self(
            traceId: $this->traceId,
            requestId: $this->requestId,
            userId: $this->userId,
            organizationId: $this->organizationId,
            applicationId: $this->applicationId,
            installationId: $installationId,
            workspaceId: $this->workspaceId,
            locale: $this->locale,
            timezone: $this->timezone,
        );
    }

    public function toArray(): array
    {
        return [
            'trace_id' => $this->traceId,
            'request_id' => $this->requestId,
            'user_id' => $this->userId,
            'organization_id' => $this->organizationId,
            'application_id' => $this->applicationId,
            'installation_id' => $this->installationId,
            'workspace_id' => $this->workspaceId,
            'locale' => $this->locale,
            'timezone' => $this->timezone,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            traceId: $data['trace_id'] ?? (string) Str::uuid(),
            requestId: $data['request_id'] ?? (string) Str::uuid(),
            userId: $data['user_id'] ?? '',
            organizationId: $data['organization_id'] ?? '',
            applicationId: $data['application_id'] ?? '',
            installationId: $data['installation_id'] ?? null,
            workspaceId: $data['workspace_id'] ?? '',
            locale: $data['locale'] ?? 'en',
            timezone: $data['timezone'] ?? 'UTC',
        );
    }
}
