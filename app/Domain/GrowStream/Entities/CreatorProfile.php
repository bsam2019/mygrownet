<?php

declare(strict_types=1);

namespace App\Domain\GrowStream\Entities;

use App\Domain\GrowStream\ValueObjects\CreatorProfileId;
use App\Domain\GrowStream\ValueObjects\CreatorTier;

final class CreatorProfile
{
    private function __construct(
        private ?CreatorProfileId $id,
        private int $userId,
        private string $displayName,
        private ?string $bio,
        private ?string $avatarUrl,
        private ?string $bannerUrl,
        private CreatorTier $tier,
        private int $totalViews,
        private int $totalVideos,
        private int $totalSubscribers,
        private bool $isVerified,
        private array $socialLinks,
        private ?\DateTimeImmutable $createdAt,
        private ?\DateTimeImmutable $updatedAt,
    ) {}

    public static function create(
        int $userId,
        string $displayName,
        CreatorTier $tier = CreatorTier::Bronze,
    ): self {
        return new self(
            id: null,
            userId: $userId,
            displayName: $displayName,
            bio: null,
            avatarUrl: null,
            bannerUrl: null,
            tier: $tier,
            totalViews: 0,
            totalVideos: 0,
            totalSubscribers: 0,
            isVerified: false,
            socialLinks: [],
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );
    }

    public static function reconstitute(array $data): self
    {
        return new self(
            id: isset($data['id']) ? CreatorProfileId::fromInt($data['id']) : null,
            userId: (int) $data['user_id'],
            displayName: $data['display_name'],
            bio: $data['bio'] ?? null,
            avatarUrl: $data['avatar_url'] ?? null,
            bannerUrl: $data['banner_url'] ?? null,
            tier: CreatorTier::fromString($data['tier']),
            totalViews: (int) ($data['total_views'] ?? 0),
            totalVideos: (int) ($data['total_videos'] ?? 0),
            totalSubscribers: (int) ($data['total_subscribers'] ?? 0),
            isVerified: (bool) ($data['is_verified'] ?? false),
            socialLinks: isset($data['social_links']) ? (is_string($data['social_links']) ? json_decode($data['social_links'], true) : $data['social_links']) : [],
            createdAt: isset($data['created_at']) ? new \DateTimeImmutable($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? new \DateTimeImmutable($data['updated_at']) : null,
        );
    }

    public function verify(): void
    {
        $this->isVerified = true;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function unverify(): void
    {
        $this->isVerified = false;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function updateDisplayName(string $displayName): void
    {
        $this->displayName = $displayName;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function updateBio(?string $bio): void
    {
        $this->bio = $bio;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function updateAvatar(string $url): void
    {
        $this->avatarUrl = $url;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function updateBanner(string $url): void
    {
        $this->bannerUrl = $url;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function updateSocialLinks(array $links): void
    {
        $this->socialLinks = $links;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function incrementTotalViews(int $count = 1): void
    {
        $this->totalViews += $count;
    }

    public function incrementTotalVideos(int $count = 1): void
    {
        $this->totalVideos += $count;
    }

    public function incrementTotalSubscribers(int $count = 1): void
    {
        $this->totalSubscribers += $count;
    }

    public function promoteTo(CreatorTier $newTier): void
    {
        $order = [CreatorTier::Bronze, CreatorTier::Silver, CreatorTier::Gold, CreatorTier::Platinum];
        $currentIndex = array_search($this->tier, $order, true);
        $newIndex = array_search($newTier, $order, true);

        if ($newIndex === false) {
            throw new \InvalidArgumentException("Invalid target tier: {$newTier->value}");
        }

        if ($newIndex <= $currentIndex) {
            throw new \RuntimeException(
                "Cannot promote from {$this->tier->value} to {$newTier->value}: target must be higher than current"
            );
        }

        $this->tier = $newTier;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function id(): ?CreatorProfileId
    {
        return $this->id;
    }

    public function userId(): int
    {
        return $this->userId;
    }

    public function displayName(): string
    {
        return $this->displayName;
    }

    public function bio(): ?string
    {
        return $this->bio;
    }

    public function avatarUrl(): ?string
    {
        return $this->avatarUrl;
    }

    public function bannerUrl(): ?string
    {
        return $this->bannerUrl;
    }

    public function tier(): CreatorTier
    {
        return $this->tier;
    }

    public function totalViews(): int
    {
        return $this->totalViews;
    }

    public function totalVideos(): int
    {
        return $this->totalVideos;
    }

    public function totalSubscribers(): int
    {
        return $this->totalSubscribers;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function socialLinks(): array
    {
        return $this->socialLinks;
    }

    public function createdAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id?->toInt(),
            'user_id' => $this->userId,
            'display_name' => $this->displayName,
            'bio' => $this->bio,
            'avatar_url' => $this->avatarUrl,
            'banner_url' => $this->bannerUrl,
            'tier' => $this->tier->value,
            'total_views' => $this->totalViews,
            'total_videos' => $this->totalVideos,
            'total_subscribers' => $this->totalSubscribers,
            'is_verified' => $this->isVerified,
            'social_links' => $this->socialLinks,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
