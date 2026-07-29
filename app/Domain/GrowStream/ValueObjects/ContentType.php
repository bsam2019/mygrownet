<?php

declare(strict_types=1);

namespace App\Domain\GrowStream\ValueObjects;

enum ContentType: string
{
    case Movie = 'movie';
    case Series = 'series';
    case Episode = 'episode';
    case Lesson = 'lesson';
    case Short = 'short';
    case Workshop = 'workshop';
    case Webinar = 'webinar';

    public function label(): string
    {
        return match ($this) {
            self::Movie => 'Movie',
            self::Series => 'Series',
            self::Episode => 'Episode',
            self::Lesson => 'Lesson',
            self::Short => 'Short',
            self::Workshop => 'Workshop',
            self::Webinar => 'Webinar',
        };
    }

    public function color(): ?string
    {
        return match ($this) {
            self::Movie => '#ef4444',
            self::Series => '#3b82f6',
            self::Episode => '#22c55e',
            self::Lesson => '#f59e0b',
            self::Short => '#ec4899',
            self::Workshop => '#8b5cf6',
            self::Webinar => '#14b8a6',
        };
    }

    public static function fromString(string $value): self
    {
        foreach (self::cases() as $case) {
            if (strcasecmp($case->value, $value) === 0) {
                return $case;
            }
        }
        throw new \InvalidArgumentException("Unknown ContentType: {$value}");
    }

    public static function all(): array
    {
        return array_map(fn (self $case) => [
            'value' => $case->value,
            'label' => $case->label(),
        ], self::cases());
    }
}
