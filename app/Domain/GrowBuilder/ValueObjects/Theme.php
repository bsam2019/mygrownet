<?php

declare(strict_types=1);

namespace App\Domain\GrowBuilder\ValueObjects;

final class Theme
{
    private function __construct(
        private string $primaryColor,
        private string $secondaryColor,
        private string $accentColor,
        private string $backgroundColor,
        private string $textColor,
        private string $headingFont,
        private string $bodyFont,
        private int $borderRadius,
    ) {}

    public static function create(
        string $primaryColor = '#2563eb',
        string $secondaryColor = '#64748b',
        string $accentColor = '#059669',
        string $backgroundColor = '#ffffff',
        string $textColor = '#1f2937',
        string $headingFont = 'Inter',
        string $bodyFont = 'Inter',
        int $borderRadius = 8,
    ): self {
        return new self(
            $primaryColor,
            $secondaryColor,
            $accentColor,
            $backgroundColor,
            $textColor,
            $headingFont,
            $bodyFont,
            $borderRadius,
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['primaryColor'] ?? '#2563eb',
            $data['secondaryColor'] ?? '#64748b',
            $data['accentColor'] ?? '#059669',
            $data['backgroundColor'] ?? '#ffffff',
            $data['textColor'] ?? '#1f2937',
            $data['headingFont'] ?? 'Inter',
            $data['bodyFont'] ?? 'Inter',
            $data['borderRadius'] ?? 8,
        );
    }

    public function toArray(): array
    {
        return [
            'primaryColor' => $this->primaryColor,
            'secondaryColor' => $this->secondaryColor,
            'accentColor' => $this->accentColor,
            'backgroundColor' => $this->backgroundColor,
            'textColor' => $this->textColor,
            'headingFont' => $this->headingFont,
            'bodyFont' => $this->bodyFont,
            'borderRadius' => $this->borderRadius,
        ];
    }

    public function withPrimaryColor(string $color): self
    {
        return new self(
            $color,
            $this->secondaryColor,
            $this->accentColor,
            $this->backgroundColor,
            $this->textColor,
            $this->headingFont,
            $this->bodyFont,
            $this->borderRadius,
        );
    }

    public function getPrimaryColor(): string { return $this->primaryColor; }
    public function getSecondaryColor(): string { return $this->secondaryColor; }
    public function getAccentColor(): string { return $this->accentColor; }
    public function getBackgroundColor(): string { return $this->backgroundColor; }
    public function getTextColor(): string { return $this->textColor; }
    public function getHeadingFont(): string { return $this->headingFont; }
    public function getBodyFont(): string { return $this->bodyFont; }
    public function getBorderRadius(): int { return $this->borderRadius; }
}
