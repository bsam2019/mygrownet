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
    
    // Advanced templates from Design Studio
    case ADVANCED_CLASSIC = 'advanced-classic';
    case ADVANCED_PROFESSIONAL = 'advanced-professional';
    case ADVANCED_MINIMAL = 'advanced-minimal';

    public function label(): string
    {
        return match ($this) {
            self::CLASSIC => 'Classic',
            self::MODERN => 'Modern',
            self::MINIMAL => 'Minimal',
            self::PROFESSIONAL => 'Professional',
            self::BOLD => 'Bold',
            self::ADVANCED_CLASSIC => 'Classic (Advanced)',
            self::ADVANCED_PROFESSIONAL => 'Professional Modern (Advanced)',
            self::ADVANCED_MINIMAL => 'Minimal (Advanced)',
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
            self::ADVANCED_CLASSIC => 'Clean professional layout with customizable blocks',
            self::ADVANCED_PROFESSIONAL => 'Bold heading with payment details and terms section',
            self::ADVANCED_MINIMAL => 'Simple clean invoice with underline table style',
        };
    }
    
    public function isAdvanced(): bool
    {
        return in_array($this, [
            self::ADVANCED_CLASSIC,
            self::ADVANCED_PROFESSIONAL,
            self::ADVANCED_MINIMAL,
        ]);
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
