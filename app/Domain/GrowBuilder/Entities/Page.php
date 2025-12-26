<?php

declare(strict_types=1);

namespace App\Domain\GrowBuilder\Entities;

use App\Domain\GrowBuilder\ValueObjects\PageId;
use App\Domain\GrowBuilder\ValueObjects\PageContent;
use DateTimeImmutable;

class Page
{
    private function __construct(
        private ?PageId $id,
        private int $siteId,
        private string $title,
        private string $slug,
        private PageContent $content,
        private ?string $metaTitle,
        private ?string $metaDescription,
        private ?string $ogImage,
        private bool $isHomepage,
        private bool $isPublished,
        private bool $showInNav,
        private int $navOrder,
        private DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt,
    ) {}

    public static function create(
        int $siteId,
        string $title,
        string $slug,
        PageContent $content,
        bool $isHomepage = false,
        bool $showInNav = true,
    ): self {
        $now = new DateTimeImmutable();

        return new self(
            id: null,
            siteId: $siteId,
            title: $title,
            slug: self::normalizeSlug($slug),
            content: $content,
            metaTitle: null,
            metaDescription: null,
            ogImage: null,
            isHomepage: $isHomepage,
            isPublished: false,
            showInNav: $showInNav,
            navOrder: 0,
            createdAt: $now,
            updatedAt: $now,
        );
    }

    public static function reconstitute(
        PageId $id,
        int $siteId,
        string $title,
        string $slug,
        PageContent $content,
        ?string $metaTitle,
        ?string $metaDescription,
        ?string $ogImage,
        bool $isHomepage,
        bool $isPublished,
        bool $showInNav,
        int $navOrder,
        DateTimeImmutable $createdAt,
        DateTimeImmutable $updatedAt,
    ): self {
        return new self(
            $id, $siteId, $title, $slug, $content, $metaTitle,
            $metaDescription, $ogImage, $isHomepage, $isPublished,
            $showInNav, $navOrder, $createdAt, $updatedAt
        );
    }

    public function updateTitle(string $title): void
    {
        $this->title = $title;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function updateSlug(string $slug): void
    {
        $this->slug = self::normalizeSlug($slug);
        $this->updatedAt = new DateTimeImmutable();
    }

    public function updateContent(PageContent $content): void
    {
        $this->content = $content;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function updateSeo(?string $metaTitle, ?string $metaDescription, ?string $ogImage = null): void
    {
        $this->metaTitle = $metaTitle;
        $this->metaDescription = $metaDescription;
        $this->ogImage = $ogImage;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function publish(): void
    {
        $this->isPublished = true;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function unpublish(): void
    {
        $this->isPublished = false;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function setAsHomepage(): void
    {
        $this->isHomepage = true;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function unsetAsHomepage(): void
    {
        $this->isHomepage = false;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function setNavVisibility(bool $showInNav, int $order = 0): void
    {
        $this->showInNav = $showInNav;
        $this->navOrder = $order;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function setShowInNav(bool $showInNav): void
    {
        $this->showInNav = $showInNav;
        $this->updatedAt = new DateTimeImmutable();
    }

    private static function normalizeSlug(string $slug): string
    {
        $slug = strtolower(trim($slug));
        $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        return trim($slug, '-');
    }

    // Getters
    public function getId(): ?PageId { return $this->id; }
    public function getSiteId(): int { return $this->siteId; }
    public function getTitle(): string { return $this->title; }
    public function getSlug(): string { return $this->slug; }
    public function getContent(): PageContent { return $this->content; }
    public function getMetaTitle(): ?string { return $this->metaTitle; }
    public function getMetaDescription(): ?string { return $this->metaDescription; }
    public function getOgImage(): ?string { return $this->ogImage; }
    public function isHomepage(): bool { return $this->isHomepage; }
    public function isPublished(): bool { return $this->isPublished; }
    public function showInNav(): bool { return $this->showInNav; }
    public function getNavOrder(): int { return $this->navOrder; }
    public function getCreatedAt(): DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): DateTimeImmutable { return $this->updatedAt; }

    public function getEffectiveMetaTitle(): string
    {
        return $this->metaTitle ?? $this->title;
    }
}
