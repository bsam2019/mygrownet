<?php

declare(strict_types=1);

namespace App\Domain\GrowStream\ValueObjects;

enum PlaybackPolicy: string
{
    case Public = 'public';
    case Signed = 'signed';

    public function label(): string
    {
        return match ($this) {
            self::Public => 'Public',
            self::Signed => 'Signed URL',
        };
    }

    public function color(): ?string
    {
        return match ($this) {
            self::Public => '#22c55e',
            self::Signed => '#3b82f6',
        };
    }

    public static function fromString(string $value): self
    {
        foreach (self::cases() as $case) {
            if (strcasecmp($case->value, $value) === 0) {
                return $case;
            }
        }
        throw new \InvalidArgumentException("Unknown PlaybackPolicy: {$value}");
    }

    public static function all(): array
    {
        return array_map(fn (self $case) => [
            'value' => $case->value,
            'label' => $case->label(),
        ], self::cases());
    }
}
