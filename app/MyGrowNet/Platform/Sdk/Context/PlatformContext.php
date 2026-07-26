<?php

namespace MyGrowNet\Platform\Sdk\Context;

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

    public static function fromCore(\App\Domain\Core\ValueObjects\PlatformContext $core): self
    {
        return new self(
            traceId: $core->traceId,
            requestId: $core->requestId,
            userId: $core->userId,
            organizationId: $core->organizationId,
            applicationId: $core->applicationId,
            installationId: $core->installationId ?? null,
            workspaceId: $core->workspaceId ?? '',
            locale: $core->locale ?? 'en',
            timezone: $core->timezone ?? 'UTC',
        );
    }

    public function toCore(): \App\Domain\Core\ValueObjects\PlatformContext
    {
        return \App\Domain\Core\ValueObjects\PlatformContext::make(
            userId: $this->userId,
            organizationId: $this->organizationId,
            applicationId: $this->applicationId,
            traceId: $this->traceId,
            requestId: $this->requestId,
            installationId: $this->installationId,
            workspaceId: $this->workspaceId,
            locale: $this->locale,
            timezone: $this->timezone,
        );
    }

    public static function current(): self
    {
        $core = \App\Domain\Core\ValueObjects\PlatformContext::resolve();
        return self::fromCore($core);
    }
}
