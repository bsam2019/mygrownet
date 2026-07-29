<?php

declare(strict_types=1);

namespace App\Domain\GrowStream\ValueObjects;

enum StarterKitTier: string
{
    case Basic = 'basic';
    case Premium = 'premium';
    case Elite = 'elite';
    case All = 'all';

    public function label(): string
    {
        return match ($this) {
            self::Basic => 'Basic',
            self::Premium => 'Premium',
            self::Elite => 'Elite',
            self::All => 'All Tiers',
        };
    }

    public function color(): ?string
    {
        return match ($this) {
            self::Basic => '#22c55e',
            self::Premium => '#3b82f6',
            self::Elite => '#f59e0b',
            self::All => '#8b5cf6',
        };
    }

    public static function fromString(string $value): self
    {
        foreach (self::cases() as $case) {
            if (strcasecmp($case->value, $value) === 0) {
                return $case;
            }
        }
        throw new \InvalidArgumentException("Unknown StarterKitTier: {$value}");
    }

    public static function all(): array
    {
        return array_map(fn (self $case) => [
            'value' => $case->value,
            'label' => $case->label(),
        ], self::cases());
    }
}
