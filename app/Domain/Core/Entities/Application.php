<?php

namespace App\Domain\Core\Entities;

class Application
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $slug,
        public readonly string $type,
        public readonly ?string $url,
        public readonly bool $isActive,
        public readonly string $category,
        public readonly string $accessModel,
        public readonly string $contextSupport,
        public readonly bool $requiresOrganizationContext,
        public readonly bool $subscriptionRequired,
        public readonly string $lifecycle,
        public readonly string $operationalStatus,
        public readonly ?string $replacementAppId,
        public readonly ?string $migrationDeadline,
        public readonly bool $isVisible,
        public readonly array $config,
    ) {}
}
