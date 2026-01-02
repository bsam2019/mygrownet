<?php

declare(strict_types=1);

namespace App\Domain\QuickInvoice\ValueObjects;

final class ThemeColors
{
    private function __construct(
        private readonly string $primary,
        private readonly string $secondary,
        private readonly string $accent,
        private readonly string $text,
        private readonly string $background
    ) {}

    public static function default(): self
    {
        return new self(
            primary: '#2563eb',    // Blue
            secondary: '#1e40af',  // Dark blue
            accent: '#059669',     // Green
            text: '#1f2937',       // Dark gray
            background: '#ffffff'  // White
        );
    }

    public static function create(
        string $primary,
        ?string $secondary = null,
        ?string $accent = null,
        ?string $text = null,
        ?string $background = null
    ): self {
        // Auto-generate secondary from primary if not provided
        $secondary = $secondary ?? self::darken($primary, 20);
        
        return new self(
            primary: self::validateColor($primary),
            secondary: self::validateColor($secondary),
            accent: self::validateColor($accent ?? '#059669'),
            text: self::validateColor($text ?? '#1f2937'),
            background: self::validateColor($background ?? '#ffffff')
        );
    }

    public static function fromArray(array $data): self
    {
        return self::create(
            primary: $data['primary'] ?? '#2563eb',
            secondary: $data['secondary'] ?? null,
            accent: $data['accent'] ?? null,
            text: $data['text'] ?? null,
            background: $data['background'] ?? null
        );
    }

    private static function validateColor(string $color): string
    {
        // Ensure it's a valid hex color
        if (!preg_match('/^#[a-fA-F0-9]{6}$/', $color)) {
            return '#2563eb'; // Default to blue if invalid
        }
        return strtolower($color);
    }

    private static function darken(string $hex, int $percent): string
    {
        $hex = ltrim($hex, '#');
        $r = max(0, hexdec(substr($hex, 0, 2)) - (255 * $percent / 100));
        $g = max(0, hexdec(substr($hex, 2, 2)) - (255 * $percent / 100));
        $b = max(0, hexdec(substr($hex, 4, 2)) - (255 * $percent / 100));
        return sprintf('#%02x%02x%02x', $r, $g, $b);
    }

    public function primary(): string { return $this->primary; }
    public function secondary(): string { return $this->secondary; }
    public function accent(): string { return $this->accent; }
    public function text(): string { return $this->text; }
    public function background(): string { return $this->background; }

    public function toArray(): array
    {
        return [
            'primary' => $this->primary,
            'secondary' => $this->secondary,
            'accent' => $this->accent,
            'text' => $this->text,
            'background' => $this->background,
        ];
    }

    public function toCss(): string
    {
        return "
            --color-primary: {$this->primary};
            --color-secondary: {$this->secondary};
            --color-accent: {$this->accent};
            --color-text: {$this->text};
            --color-background: {$this->background};
        ";
    }
}
