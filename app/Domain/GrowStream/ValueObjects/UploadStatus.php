<?php

declare(strict_types=1);

namespace App\Domain\GrowStream\ValueObjects;

enum UploadStatus: string
{
    case Pending = 'pending';
    case Uploading = 'uploading';
    case Processing = 'processing';
    case Ready = 'ready';
    case Failed = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Uploading => 'Uploading',
            self::Processing => 'Processing',
            self::Ready => 'Ready',
            self::Failed => 'Failed',
        };
    }

    public function color(): ?string
    {
        return match ($this) {
            self::Pending => '#f59e0b',
            self::Uploading => '#3b82f6',
            self::Processing => '#8b5cf6',
            self::Ready => '#22c55e',
            self::Failed => '#ef4444',
        };
    }

    public static function fromString(string $value): self
    {
        foreach (self::cases() as $case) {
            if (strcasecmp($case->value, $value) === 0) {
                return $case;
            }
        }
        throw new \InvalidArgumentException("Unknown UploadStatus: {$value}");
    }

    public static function all(): array
    {
        return array_map(fn (self $case) => [
            'value' => $case->value,
            'label' => $case->label(),
        ], self::cases());
    }
}
