<?php

declare(strict_types=1);

namespace App\Domain\GrowBuilder\Entities;

use App\Domain\GrowBuilder\ValueObjects\SiteId;
use App\Domain\GrowBuilder\ValueObjects\Subdomain;
use App\Domain\GrowBuilder\ValueObjects\SiteStatus;
use App\Domain\GrowBuilder\ValueObjects\SitePlan;
use App\Domain\GrowBuilder\ValueObjects\Theme;
use DateTimeImmutable;

class Site
{
    private function __construct(
        private ?SiteId $id,
        private int $userId,
        private ?int $templateId,
        private string $name,
        private Subdomain $subdomain,
        private ?string $customDomain,
        private ?string $description,
        private ?string $logo,
        private ?string $favicon,
        private array $settings,
        private ?Theme $theme,
        private array $socialLinks,
        private array $contactInfo,
        private array $businessHours,
        private array $seoSettings,
        private SiteStatus $status,
        private SitePlan $plan,
        private ?DateTimeImmutable $publishedAt,
        private ?DateTimeImmutable $planExpiresAt,
        private DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt,
    ) {}

    public static function create(
        int $userId,
        string $name,
        Subdomain $subdomain,
        ?int $templateId = null,
        ?string $description = null,
    ): self {
        $now = new DateTimeImmutable();

        return new self(
            id: null,
            userId: $userId,
            templateId: $templateId,
            name: $name,
            subdomain: $subdomain,
            customDomain: null,
            description: $description,
            logo: null,
            favicon: null,
            settings: [],
            theme: null,
            socialLinks: [],
            contactInfo: [],
            businessHours: [],
            seoSettings: [],
            status: SiteStatus::draft(),
            plan: SitePlan::starter(),
            publishedAt: null,
            planExpiresAt: null,
            createdAt: $now,
            updatedAt: $now,
        );
    }

    public static function reconstitute(
        SiteId $id,
        int $userId,
        ?int $templateId,
        string $name,
        Subdomain $subdomain,
        ?string $customDomain,
        ?string $description,
        ?string $logo,
        ?string $favicon,
        array $settings,
        ?Theme $theme,
        array $socialLinks,
        array $contactInfo,
        array $businessHours,
        array $seoSettings,
        SiteStatus $status,
        SitePlan $plan,
        ?DateTimeImmutable $publishedAt,
        ?DateTimeImmutable $planExpiresAt,
        DateTimeImmutable $createdAt,
        DateTimeImmutable $updatedAt,
    ): self {
        return new self(
            $id, $userId, $templateId, $name, $subdomain, $customDomain,
            $description, $logo, $favicon, $settings, $theme, $socialLinks,
            $contactInfo, $businessHours, $seoSettings, $status, $plan,
            $publishedAt, $planExpiresAt, $createdAt, $updatedAt
        );
    }

    public function publish(): void
    {
        if ($this->status->isPublished()) {
            return;
        }

        $this->status = SiteStatus::published();
        $this->publishedAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function unpublish(): void
    {
        $this->status = SiteStatus::draft();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function suspend(): void
    {
        $this->status = SiteStatus::suspended();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function updateName(string $name): void
    {
        $this->name = $name;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function updateDescription(?string $description): void
    {
        $this->description = $description;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function updateLogo(?string $logo): void
    {
        $this->logo = $logo;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function updateFavicon(?string $favicon): void
    {
        $this->favicon = $favicon;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function updateTheme(Theme $theme): void
    {
        $this->theme = $theme;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function updateContactInfo(array $contactInfo): void
    {
        $this->contactInfo = $contactInfo;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function updateSocialLinks(array $socialLinks): void
    {
        $this->socialLinks = $socialLinks;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function updateSeoSettings(array $seoSettings): void
    {
        $this->seoSettings = $seoSettings;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function setCustomDomain(?string $domain): void
    {
        $this->customDomain = $domain;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function upgradePlan(SitePlan $plan, DateTimeImmutable $expiresAt): void
    {
        $this->plan = $plan;
        $this->planExpiresAt = $expiresAt;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function setLogo(?string $logo): void
    {
        $this->logo = $logo;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function setFavicon(?string $favicon): void
    {
        $this->favicon = $favicon;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function setSettings(array $settings): void
    {
        $this->settings = $settings;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function canPublish(): bool
    {
        return !$this->status->isSuspended();
    }

    public function isPublished(): bool
    {
        return $this->status->isPublished();
    }

    public function isPlanExpired(): bool
    {
        if ($this->planExpiresAt === null) {
            return false;
        }
        return $this->planExpiresAt < new DateTimeImmutable();
    }

    // Getters
    public function getId(): ?SiteId { return $this->id; }
    public function getUserId(): int { return $this->userId; }
    public function getTemplateId(): ?int { return $this->templateId; }
    public function getName(): string { return $this->name; }
    public function getSubdomain(): Subdomain { return $this->subdomain; }
    public function getCustomDomain(): ?string { return $this->customDomain; }
    public function getDescription(): ?string { return $this->description; }
    public function getLogo(): ?string { return $this->logo; }
    public function getFavicon(): ?string { return $this->favicon; }
    public function getSettings(): array { return $this->settings; }
    public function getTheme(): ?Theme { return $this->theme; }
    public function getSocialLinks(): array { return $this->socialLinks; }
    public function getContactInfo(): array { return $this->contactInfo; }
    public function getBusinessHours(): array { return $this->businessHours; }
    public function getSeoSettings(): array { return $this->seoSettings; }
    public function getStatus(): SiteStatus { return $this->status; }
    public function getPlan(): SitePlan { return $this->plan; }
    public function getPublishedAt(): ?DateTimeImmutable { return $this->publishedAt; }
    public function getPlanExpiresAt(): ?DateTimeImmutable { return $this->planExpiresAt; }
    public function getCreatedAt(): DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): DateTimeImmutable { return $this->updatedAt; }

    public function getUrl(): string
    {
        if ($this->customDomain) {
            $scheme = app()->environment('local') ? 'http' : 'https';
            return $scheme . '://' . $this->customDomain;
        }
        
        // For local development, use APP_URL with subdomain simulation
        if (app()->environment('local', 'development')) {
            $appUrl = config('app.url', 'http://127.0.0.1:8001');
            return $appUrl . '/sites/' . $this->subdomain->value();
        }
        
        return 'https://' . $this->subdomain->value() . '.mygrownet.com';
    }
}
