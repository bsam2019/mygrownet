<?php

declare(strict_types=1);

namespace App\Domain\GrowStream\ValueObjects;

enum ContentRating: string
{
    case G = 'G';
    case PG = 'PG';
    case PG13 = 'PG-13';
    case R = 'R';
    case NR = 'NR';

    public function label(): string
    {
        return match ($this) {
            self::G => 'General Audiences',
            self::PG => 'Parental Guidance',
            self::PG13 => 'Parents Strongly Cautioned',
            self::R => 'Restricted',
            self::NR => 'Not Rated',
        };
    }

    public function color(): ?string
    {
        return match ($this) {
            self::G => '#22c55e',
            self::PG => '#3b82f6',
            self::PG13 => '#f59e0b',
            self::R => '#ef4444',
            self::NR => '#6b7280',
        };
    }

    public static function fromString(string $value): self
    {
        foreach (self::cases() as $case) {
            if (strcasecmp($case->value, $value) === 0) {
                return $case;
            }
        }
        throw new \InvalidArgumentException("Unknown ContentRating: {$value}");
    }

    public static function all(): array
    {
        return array_map(fn (self $case) => [
            'value' => $case->value,
            'label' => $case->label(),
        ], self::cases());
    }
}
