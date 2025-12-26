<?php

declare(strict_types=1);

namespace App\Application\GrowBuilder\DTOs;

final class UpdateSiteDTO
{
    public function __construct(
        public readonly int $siteId,
        public readonly int $userId,
        public readonly ?string $name = null,
        public readonly ?string $description = null,
        public readonly ?array $theme = null,
        public readonly ?array $contactInfo = null,
        public readonly ?array $socialLinks = null,
        public readonly ?array $seoSettings = null,
        public readonly ?string $customDomain = null,
        public readonly ?string $logo = null,
        public readonly ?string $favicon = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            siteId: $data['site_id'],
            userId: $data['user_id'],
            name: $data['name'] ?? null,
            description: $data['description'] ?? null,
            theme: $data['theme'] ?? null,
            contactInfo: $data['contact_info'] ?? null,
            socialLinks: $data['social_links'] ?? null,
            seoSettings: $data['seo_settings'] ?? null,
            customDomain: $data['custom_domain'] ?? null,
            logo: $data['logo'] ?? null,
            favicon: $data['favicon'] ?? null,
        );
    }
}
