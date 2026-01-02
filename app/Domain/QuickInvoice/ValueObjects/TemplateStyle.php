<?php

declare(strict_types=1);

namespace App\Domain\QuickInvoice\ValueObjects;

enum TemplateStyle: string
{
    case CLASSIC = 'classic';
    case MODERN = 'modern';
    case MINIMAL = 'minimal';
    case PROFESSIONAL = 'professional';
    case BOLD = 'bold';

    public function label(): string
    {
        return match ($this) {
            self::CLASSIC => 'Classic',
            self::MODERN => 'Modern',
            self::MINIMAL => 'Minimal',
            self::PROFESSIONAL => 'Professional',
            self::BOLD => 'Bold',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::CLASSIC => 'Traditional business style with clean lines',
            self::MODERN => 'Contemporary design with accent colors',
            self::MINIMAL => 'Simple and elegant with lots of whitespace',
            self::PROFESSIONAL => 'Corporate style with structured layout',
            self::BOLD => 'Eye-catching design with strong colors',
        };
    }

    public function previewImage(): string
    {
        return "/images/invoice-templates/{$this->value}.png";
    }

    public static function all(): array
    {
        return array_map(fn($case) => [
            'value' => $case->value,
            'label' => $case->label(),
            'description' => $case->description(),
        ], self::cases());
    }
}
