<?php

declare(strict_types=1);

namespace App\Application\GrowBuilder\DTOs;

final class SavePageContentDTO
{
    public function __construct(
        public readonly int $siteId,
        public readonly int $userId,
        public readonly array $sections,
        public readonly ?int $pageId = null,
        public readonly ?string $title = null,
        public readonly ?string $slug = null,
        public readonly ?string $metaTitle = null,
        public readonly ?string $metaDescription = null,
        public readonly ?string $ogImage = null,
        public readonly bool $isHomepage = false,
        public readonly bool $showInNav = true,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            siteId: $data['site_id'],
            userId: $data['user_id'],
            sections: $data['sections'] ?? [],
            pageId: $data['page_id'] ?? null,
            title: $data['title'] ?? null,
            slug: $data['slug'] ?? null,
            metaTitle: $data['meta_title'] ?? null,
            metaDescription: $data['meta_description'] ?? null,
            ogImage: $data['og_image'] ?? null,
            isHomepage: $data['is_homepage'] ?? false,
            showInNav: $data['show_in_nav'] ?? true,
        );
    }
}
