<?php

namespace App\Domain\PlatformBilling\Entities;

class SubscriptionPlan
{
    private function __construct(
        private readonly ?int $id,
        private string $name,
        private string $slug,
        private float $monthlyPrice,
        private float $annualPrice,
        private int $siteLimit,
        private int $storageLimitMb,
        private int $teamMemberLimit,
        private ?int $clientLimit,
        private array $features,
        private bool $isActive,
        private int $sortOrder,
        private ?\DateTimeImmutable $createdAt,
        private ?\DateTimeImmutable $updatedAt,
    ) {}

    public static function create(
        string $name,
        string $slug,
        float $monthlyPrice,
        float $annualPrice,
        int $siteLimit,
        int $storageLimitMb,
        int $teamMemberLimit,
        ?int $clientLimit = null,
        array $features = [],
        int $sortOrder = 0,
    ): self {
        return new self(
            id: null,
            name: $name,
            slug: $slug,
            monthlyPrice: $monthlyPrice,
            annualPrice: $annualPrice,
            siteLimit: $siteLimit,
            storageLimitMb: $storageLimitMb,
            teamMemberLimit: $teamMemberLimit,
            clientLimit: $clientLimit,
            features: $features,
            isActive: true,
            sortOrder: $sortOrder,
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );
    }

    public static function reconstitute(
        int $id,
        string $name,
        string $slug,
        float $monthlyPrice,
        float $annualPrice,
        int $siteLimit,
        int $storageLimitMb,
        int $teamMemberLimit,
        ?int $clientLimit,
        array $features,
        bool $isActive,
        int $sortOrder,
        \DateTimeImmutable $createdAt,
        \DateTimeImmutable $updatedAt,
    ): self {
        return new self(
            id: $id,
            name: $name,
            slug: $slug,
            monthlyPrice: $monthlyPrice,
            annualPrice: $annualPrice,
            siteLimit: $siteLimit,
            storageLimitMb: $storageLimitMb,
            teamMemberLimit: $teamMemberLimit,
            clientLimit: $clientLimit,
            features: $features,
            isActive: $isActive,
            sortOrder: $sortOrder,
            createdAt: $createdAt,
            updatedAt: $updatedAt,
        );
    }

    public function deactivate(): void { $this->isActive = false; }
    public function activate(): void { $this->isActive = true; }
    public function rename(string $name): void { $this->name = $name; }
    public function updatePricing(float $monthly, float $annual): void { $this->monthlyPrice = $monthly; $this->annualPrice = $annual; }

    public function hasFeature(string $feature): bool { return ($this->features[$feature] ?? false) !== false; }
    public function getFeature(string $feature, mixed $default = null): mixed { return $this->features[$feature] ?? $default; }

    public function id(): ?int { return $this->id; }
    public function name(): string { return $this->name; }
    public function slug(): string { return $this->slug; }
    public function monthlyPrice(): float { return $this->monthlyPrice; }
    public function annualPrice(): float { return $this->annualPrice; }
    public function siteLimit(): int { return $this->siteLimit; }
    public function storageLimitMb(): int { return $this->storageLimitMb; }
    public function teamMemberLimit(): int { return $this->teamMemberLimit; }
    public function clientLimit(): ?int { return $this->clientLimit; }
    public function features(): array { return $this->features; }
    public function isActive(): bool { return $this->isActive; }
    public function sortOrder(): int { return $this->sortOrder; }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'monthly_price' => $this->monthlyPrice,
            'annual_price' => $this->annualPrice,
            'site_limit' => $this->siteLimit,
            'storage_limit_mb' => $this->storageLimitMb,
            'team_member_limit' => $this->teamMemberLimit,
            'client_limit' => $this->clientLimit,
            'features' => $this->features,
            'is_active' => $this->isActive,
            'sort_order' => $this->sortOrder,
        ];
    }
}
