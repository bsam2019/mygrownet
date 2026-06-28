<?php

declare(strict_types=1);

namespace App\Domain\GrowBuilder\ValueObjects;

final class TemplateCategory
{
    private const BUSINESS = 'business';
    private const RESTAURANT = 'restaurant';
    private const CHURCH = 'church';
    private const TUTOR = 'tutor';
    private const PORTFOLIO = 'portfolio';
    private const SALON = 'salon';
    private const SHOP = 'shop';
    private const SERVICE = 'service';

    private const ALL = [
        self::BUSINESS,
        self::RESTAURANT,
        self::CHURCH,
        self::TUTOR,
        self::PORTFOLIO,
        self::SALON,
        self::SHOP,
        self::SERVICE,
    ];

    private const LABELS = [
        self::BUSINESS => 'Business',
        self::RESTAURANT => 'Restaurant & Food',
        self::CHURCH => 'Church & Religious',
        self::TUTOR => 'Education & Tutoring',
        self::PORTFOLIO => 'Portfolio',
        self::SALON => 'Salon & Beauty',
        self::SHOP => 'Shop & Retail',
        self::SERVICE => 'Service Provider',
    ];

    private function __construct(private string $value) {}

    public static function business(): self { return new self(self::BUSINESS); }
    public static function restaurant(): self { return new self(self::RESTAURANT); }
    public static function church(): self { return new self(self::CHURCH); }
    public static function tutor(): self { return new self(self::TUTOR); }
    public static function portfolio(): self { return new self(self::PORTFOLIO); }
    public static function salon(): self { return new self(self::SALON); }
    public static function shop(): self { return new self(self::SHOP); }
    public static function service(): self { return new self(self::SERVICE); }

    public static function fromString(string $value): self
    {
        if (!in_array($value, self::ALL, true)) {
            throw new \InvalidArgumentException("Invalid template category: {$value}");
        }
        return new self($value);
    }

    public static function all(): array
    {
        return array_map(fn($v) => new self($v), self::ALL);
    }

    public static function allWithLabels(): array
    {
        return self::LABELS;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function label(): string
    {
        return self::LABELS[$this->value];
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
