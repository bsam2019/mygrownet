<?php

namespace App\Domain\Wedding\ValueObjects;

class VendorCategory
{
    private const VENUE = 'venue';
    private const PHOTOGRAPHY = 'photography';
    private const CATERING = 'catering';
    private const DECORATION = 'decoration';
    private const MUSIC = 'music';
    private const TRANSPORT = 'transport';
    private const FLOWERS = 'flowers';
    private const MAKEUP = 'makeup';
    private const PLANNING = 'planning';

    private const VALID_CATEGORIES = [
        self::VENUE,
        self::PHOTOGRAPHY,
        self::CATERING,
        self::DECORATION,
        self::MUSIC,
        self::TRANSPORT,
        self::FLOWERS,
        self::MAKEUP,
        self::PLANNING
    ];

    private const CATEGORY_LABELS = [
        self::VENUE => 'Venues',
        self::PHOTOGRAPHY => 'Photography',
        self::CATERING => 'Catering',
        self::DECORATION => 'Decoration',
        self::MUSIC => 'Music & Entertainment',
        self::TRANSPORT => 'Transportation',
        self::FLOWERS => 'Flowers & Bouquets',
        self::MAKEUP => 'Makeup & Beauty',
        self::PLANNING => 'Wedding Planning'
    ];

    private const CATEGORY_ICONS = [
        self::VENUE => 'building-office',
        self::PHOTOGRAPHY => 'camera',
        self::CATERING => 'cake',
        self::DECORATION => 'sparkles',
        self::MUSIC => 'musical-note',
        self::TRANSPORT => 'truck',
        self::FLOWERS => 'flower',
        self::MAKEUP => 'face-smile',
        self::PLANNING => 'clipboard-document-list'
    ];

    private function __construct(
        private string $category
    ) {
        if (!in_array($category, self::VALID_CATEGORIES)) {
            throw new \InvalidArgumentException("Invalid vendor category: {$category}");
        }
    }

    public static function venue(): self
    {
        return new self(self::VENUE);
    }

    public static function photography(): self
    {
        return new self(self::PHOTOGRAPHY);
    }

    public static function catering(): self
    {
        return new self(self::CATERING);
    }

    public static function decoration(): self
    {
        return new self(self::DECORATION);
    }

    public static function music(): self
    {
        return new self(self::MUSIC);
    }

    public static function transport(): self
    {
        return new self(self::TRANSPORT);
    }

    public static function flowers(): self
    {
        return new self(self::FLOWERS);
    }

    public static function makeup(): self
    {
        return new self(self::MAKEUP);
    }

    public static function planning(): self
    {
        return new self(self::PLANNING);
    }

    public static function fromString(string $category): self
    {
        return new self($category);
    }

    public static function all(): array
    {
        return array_map(
            fn($category) => new self($category),
            self::VALID_CATEGORIES
        );
    }

    public function getValue(): string
    {
        return $this->category;
    }

    public function getLabel(): string
    {
        return self::CATEGORY_LABELS[$this->category];
    }

    public function getIcon(): string
    {
        return self::CATEGORY_ICONS[$this->category];
    }

    public function isVenue(): bool
    {
        return $this->category === self::VENUE;
    }

    public function isPhotography(): bool
    {
        return $this->category === self::PHOTOGRAPHY;
    }

    public function isCatering(): bool
    {
        return $this->category === self::CATERING;
    }

    public function equals(VendorCategory $other): bool
    {
        return $this->category === $other->category;
    }

    public function toArray(): array
    {
        return [
            'value' => $this->category,
            'label' => $this->getLabel(),
            'icon' => $this->getIcon()
        ];
    }
}