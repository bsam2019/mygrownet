<?php

declare(strict_types=1);

namespace App\Domain\GrowStream\ValueObjects;

enum DeviceType: string
{
    case Mobile = 'mobile';
    case Tablet = 'tablet';
    case Desktop = 'desktop';

    public function label(): string
    {
        return match ($this) {
            self::Mobile => 'Mobile',
            self::Tablet => 'Tablet',
            self::Desktop => 'Desktop',
        };
    }

    public function color(): ?string
    {
        return null;
    }

    public static function fromString(string $value): self
    {
        foreach (self::cases() as $case) {
            if (strcasecmp($case->value, $value) === 0) {
                return $case;
            }
        }
        throw new \InvalidArgumentException("Unknown DeviceType: {$value}");
    }

    public static function all(): array
    {
        return array_map(fn (self $case) => [
            'value' => $case->value,
            'label' => $case->label(),
        ], self::cases());
    }
}
