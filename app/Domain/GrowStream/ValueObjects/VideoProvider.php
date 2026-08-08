<?php

declare(strict_types=1);

namespace App\Domain\GrowStream\ValueObjects;

enum VideoProvider: string
{
    case Cloudflare = 'cloudflare';
    case Local = 'local';

    public function label(): string
    {
        return match ($this) {
            self::Cloudflare => 'Cloudflare Stream',
            self::Local => 'Local Storage',
        };
    }

    public function color(): ?string
    {
        return match ($this) {
            self::Cloudflare => '#f38020',
            self::Local => '#6b7280',
        };
    }

    public static function fromString(string $value): self
    {
        foreach (self::cases() as $case) {
            if (strcasecmp($case->value, $value) === 0) {
                return $case;
            }
        }
        throw new \InvalidArgumentException("Unknown VideoProvider: {$value}");
    }

    public static function all(): array
    {
        return array_map(fn (self $case) => [
            'value' => $case->value,
            'label' => $case->label(),
        ], self::cases());
    }
}
