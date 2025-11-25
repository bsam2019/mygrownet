<?php

namespace App\Domain\Wedding\Entities;

use App\Domain\Wedding\ValueObjects\VendorCategory;
use App\Domain\Wedding\ValueObjects\VendorRating;
use Carbon\Carbon;

class WeddingVendor
{
    public function __construct(
        private ?int $id,
        private int $userId,
        private string $businessName,
        private string $slug,
        private VendorCategory $category,
        private string $contactPerson,
        private string $phone,
        private string $email,
        private string $location,
        private string $description,
        private string $priceRange,
        private VendorRating $rating,
        private bool $verified,
        private bool $featured,
        private ?array $services,
        private ?array $portfolioImages,
        private ?array $availability,
        private ?Carbon $createdAt = null,
        private ?Carbon $updatedAt = null
    ) {}

    public static function create(
        int $userId,
        string $businessName,
        VendorCategory $category,
        string $contactPerson,
        string $phone,
        string $email,
        string $location,
        string $description,
        string $priceRange
    ): self {
        $slug = self::generateSlug($businessName);
        
        return new self(
            id: null,
            userId: $userId,
            businessName: $businessName,
            slug: $slug,
            category: $category,
            contactPerson: $contactPerson,
            phone: $phone,
            email: $email,
            location: $location,
            description: $description,
            priceRange: $priceRange,
            rating: VendorRating::zero(),
            verified: false,
            featured: false,
            services: null,
            portfolioImages: null,
            availability: null,
            createdAt: Carbon::now(),
            updatedAt: Carbon::now()
        );
    }

    public function verify(): void
    {
        $this->verified = true;
        $this->updatedAt = Carbon::now();
    }

    public function unverify(): void
    {
        $this->verified = false;
        $this->updatedAt = Carbon::now();
    }

    public function feature(): void
    {
        if (!$this->verified) {
            throw new \DomainException('Only verified vendors can be featured');
        }
        
        $this->featured = true;
        $this->updatedAt = Carbon::now();
    }

    public function unfeature(): void
    {
        $this->featured = false;
        $this->updatedAt = Carbon::now();
    }

    public function updateRating(float $newRating, int $reviewCount): void
    {
        $this->rating = VendorRating::fromRating($newRating, $reviewCount);
        $this->updatedAt = Carbon::now();
    }

    public function addPortfolioImage(string $imageUrl): void
    {
        $images = $this->portfolioImages ?? [];
        $images[] = $imageUrl;
        $this->portfolioImages = $images;
        $this->updatedAt = Carbon::now();
    }

    public function removePortfolioImage(string $imageUrl): void
    {
        if ($this->portfolioImages) {
            $this->portfolioImages = array_filter(
                $this->portfolioImages,
                fn($image) => $image !== $imageUrl
            );
            $this->updatedAt = Carbon::now();
        }
    }

    public function updateServices(array $services): void
    {
        $this->services = $services;
        $this->updatedAt = Carbon::now();
    }

    public function updateAvailability(array $availability): void
    {
        $this->availability = $availability;
        $this->updatedAt = Carbon::now();
    }

    public function isAvailableOn(Carbon $date): bool
    {
        if (!$this->availability) {
            return true; // No restrictions means available
        }
        
        // Check if date is in blocked dates
        $blockedDates = $this->availability['blocked_dates'] ?? [];
        return !in_array($date->format('Y-m-d'), $blockedDates);
    }

    public function hasHighRating(): bool
    {
        return $this->rating->getRating() >= 4.0;
    }

    private static function generateSlug(string $businessName): string
    {
        return strtolower(str_replace([' ', '&', ','], ['-', 'and', ''], $businessName));
    }

    // Getters
    public function getId(): ?int { return $this->id; }
    public function getUserId(): int { return $this->userId; }
    public function getBusinessName(): string { return $this->businessName; }
    public function getSlug(): string { return $this->slug; }
    public function getCategory(): VendorCategory { return $this->category; }
    public function getContactPerson(): string { return $this->contactPerson; }
    public function getPhone(): string { return $this->phone; }
    public function getEmail(): string { return $this->email; }
    public function getLocation(): string { return $this->location; }
    public function getDescription(): string { return $this->description; }
    public function getPriceRange(): string { return $this->priceRange; }
    public function getRating(): VendorRating { return $this->rating; }
    public function isVerified(): bool { return $this->verified; }
    public function isFeatured(): bool { return $this->featured; }
    public function getServices(): ?array { return $this->services; }
    public function getPortfolioImages(): ?array { return $this->portfolioImages; }
    public function getAvailability(): ?array { return $this->availability; }
    public function getCreatedAt(): ?Carbon { return $this->createdAt; }
    public function getUpdatedAt(): ?Carbon { return $this->updatedAt; }
}