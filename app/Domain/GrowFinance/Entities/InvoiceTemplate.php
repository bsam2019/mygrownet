<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\Entities;

use DateTimeImmutable;

class InvoiceTemplate
{
    public readonly ?int $id;
    public readonly int $businessId;
    public readonly string $name;
    public readonly string $slug;
    public readonly ?string $description;
    public readonly ?string $layout;
    public readonly ?array $colors;
    public readonly ?array $fonts;
    public readonly ?string $logoPosition;
    public readonly bool $showLogo;
    public readonly bool $showWatermark;
    public readonly ?string $headerText;
    public readonly ?string $footerText;
    public readonly ?string $termsText;
    public readonly ?array $customFields;
    public readonly bool $isDefault;
    public readonly bool $isActive;
    public readonly ?DateTimeImmutable $createdAt;
    public readonly ?DateTimeImmutable $updatedAt;

    public function __construct(
        ?int $id,
        int $businessId,
        string $name,
        string $slug,
        ?string $description,
        ?string $layout,
        ?array $colors,
        ?array $fonts,
        ?string $logoPosition,
        bool $showLogo,
        bool $showWatermark,
        ?string $headerText,
        ?string $footerText,
        ?string $termsText,
        ?array $customFields,
        bool $isDefault,
        bool $isActive,
        ?DateTimeImmutable $createdAt,
        ?DateTimeImmutable $updatedAt,
    ) {
        $this->id = $id;
        $this->businessId = $businessId;
        $this->name = $name;
        $this->slug = $slug;
        $this->description = $description;
        $this->layout = $layout;
        $this->colors = $colors;
        $this->fonts = $fonts;
        $this->logoPosition = $logoPosition;
        $this->showLogo = $showLogo;
        $this->showWatermark = $showWatermark;
        $this->headerText = $headerText;
        $this->footerText = $footerText;
        $this->termsText = $termsText;
        $this->customFields = $customFields;
        $this->isDefault = $isDefault;
        $this->isActive = $isActive;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public static function reconstitute(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            businessId: $data['business_id'],
            name: $data['name'],
            slug: $data['slug'],
            description: $data['description'] ?? null,
            layout: $data['layout'] ?? null,
            colors: $data['colors'] ?? null,
            fonts: $data['fonts'] ?? null,
            logoPosition: $data['logo_position'] ?? null,
            showLogo: (bool) ($data['show_logo'] ?? false),
            showWatermark: (bool) ($data['show_watermark'] ?? false),
            headerText: $data['header_text'] ?? null,
            footerText: $data['footer_text'] ?? null,
            termsText: $data['terms_text'] ?? null,
            customFields: $data['custom_fields'] ?? null,
            isDefault: (bool) ($data['is_default'] ?? false),
            isActive: (bool) ($data['is_active'] ?? false),
            createdAt: isset($data['created_at']) ? new DateTimeImmutable($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? new DateTimeImmutable($data['updated_at']) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'business_id' => $this->businessId,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'layout' => $this->layout,
            'colors' => $this->colors,
            'fonts' => $this->fonts,
            'logo_position' => $this->logoPosition,
            'show_logo' => $this->showLogo,
            'show_watermark' => $this->showWatermark,
            'header_text' => $this->headerText,
            'footer_text' => $this->footerText,
            'terms_text' => $this->termsText,
            'custom_fields' => $this->customFields,
            'is_default' => $this->isDefault,
            'is_active' => $this->isActive,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
