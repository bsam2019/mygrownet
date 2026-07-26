<?php

namespace MyGrowNet\Platform\Sdk\Identity;

class OrganizationIdentity
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly ?string $slug = null,
    ) {}

    public static function current(): ?self
    {
        $org = null;
        if (app()->has(\App\Domain\Core\ValueObjects\PlatformContext::class)) {
            $context = app(\App\Domain\Core\ValueObjects\PlatformContext::class);
            if ($context->organizationId) {
                return new self(
                    id: $context->organizationId,
                    name: '',
                    slug: '',
                );
            }
        }
        return null;
    }
}
