<?php

declare(strict_types=1);

namespace App\Domain\GrowStream\ValueObjects;

enum AccessLevel: string
{
    case Free = 'free';
    case Basic = 'basic';
    case Premium = 'premium';
    case Institutional = 'institutional';

    public function label(): string
    {
        return match ($this) {
            self::Free => 'Free',
            self::Basic => 'Basic',
            self::Premium => 'Premium',
            self::Institutional => 'Institutional',
        };
    }

    public function color(): ?string
    {
        return match ($this) {
            self::Free => '#22c55e',
            self::Basic => '#3b82f6',
            self::Premium => '#f59e0b',
            self::Institutional => '#8b5cf6',
        };
    }

    public static function fromString(string $value): self
    {
        foreach (self::cases() as $case) {
            if (strcasecmp($case->value, $value) === 0) {
                return $case;
            }
        }
        throw new \InvalidArgumentException("Unknown AccessLevel: {$value}");
    }

    public static function all(): array
    {
        return array_map(fn (self $case) => [
            'value' => $case->value,
            'label' => $case->label(),
        ], self::cases());
    }
}
