<?php

declare(strict_types=1);

namespace App\Domain\GrowStream\ValueObjects;

enum SeriesType: string
{
    case Course = 'course';
    case Show = 'show';
    case Documentary = 'documentary';
    case WorkshopSeries = 'workshop_series';

    public function label(): string
    {
        return match ($this) {
            self::Course => 'Course',
            self::Show => 'Show',
            self::Documentary => 'Documentary',
            self::WorkshopSeries => 'Workshop Series',
        };
    }

    public function color(): ?string
    {
        return match ($this) {
            self::Course => '#22c55e',
            self::Show => '#3b82f6',
            self::Documentary => '#f59e0b',
            self::WorkshopSeries => '#8b5cf6',
        };
    }

    public static function fromString(string $value): self
    {
        foreach (self::cases() as $case) {
            if (strcasecmp($case->value, $value) === 0) {
                return $case;
            }
        }
        throw new \InvalidArgumentException("Unknown SeriesType: {$value}");
    }

    public static function all(): array
    {
        return array_map(fn (self $case) => [
            'value' => $case->value,
            'label' => $case->label(),
        ], self::cases());
    }
}
