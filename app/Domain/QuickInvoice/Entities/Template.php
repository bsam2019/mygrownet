<?php

declare(strict_types=1);

namespace App\Domain\QuickInvoice\Entities;

use DateTimeImmutable;

class Template
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $userId,
        public readonly string $name,
        public readonly ?string $description,
        public readonly ?string $baseTemplate,
        public readonly ?string $primaryColor,
        public readonly ?string $secondaryColor,
        public readonly ?string $accentColor,
        public readonly ?string $fontFamily,
        public readonly ?string $headingFont,
        public readonly ?string $headerStyle,
        public readonly ?string $layoutStyle,
        public readonly ?bool $showLogo,
        public readonly ?bool $showBusinessDetails,
        public readonly ?string $logoPosition,
        public readonly ?int $logoSize,
        public readonly ?int $borderRadius,
        public readonly ?string $borderStyle,
        public readonly ?int $spacing,
        public readonly ?string $tableStyle,
        public readonly ?array $customCss,
        public readonly ?array $sectionVisibility,
        public readonly ?array $fieldLabels,
        public readonly ?array $layoutJson,
        public readonly ?array $fieldConfig,
        public readonly ?string $logoUrl,
        public readonly ?int $version,
        public readonly ?string $category,
        public readonly ?array $tags,
        public readonly ?bool $isPublic,
        public readonly ?int $usageCount,
        public readonly ?DateTimeImmutable $lastUsedAt,
        public readonly ?DateTimeImmutable $createdAt,
        public readonly ?DateTimeImmutable $updatedAt,
    ) {}

    public static function reconstitute(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            userId: $data['user_id'],
            name: $data['name'],
            description: $data['description'] ?? null,
            baseTemplate: $data['base_template'] ?? null,
            primaryColor: $data['primary_color'] ?? null,
            secondaryColor: $data['secondary_color'] ?? null,
            accentColor: $data['accent_color'] ?? null,
            fontFamily: $data['font_family'] ?? null,
            headingFont: $data['heading_font'] ?? null,
            headerStyle: $data['header_style'] ?? null,
            layoutStyle: $data['layout_style'] ?? null,
            showLogo: $data['show_logo'] ?? null,
            showBusinessDetails: $data['show_business_details'] ?? null,
            logoPosition: $data['logo_position'] ?? null,
            logoSize: $data['logo_size'] ?? null,
            borderRadius: $data['border_radius'] ?? null,
            borderStyle: $data['border_style'] ?? null,
            spacing: $data['spacing'] ?? null,
            tableStyle: $data['table_style'] ?? null,
            customCss: $data['custom_css'] ?? null,
            sectionVisibility: $data['section_visibility'] ?? null,
            fieldLabels: $data['field_labels'] ?? null,
            layoutJson: $data['layout_json'] ?? null,
            fieldConfig: $data['field_config'] ?? null,
            logoUrl: $data['logo_url'] ?? null,
            version: $data['version'] ?? null,
            category: $data['category'] ?? null,
            tags: $data['tags'] ?? null,
            isPublic: $data['is_public'] ?? null,
            usageCount: $data['usage_count'] ?? null,
            lastUsedAt: isset($data['last_used_at']) ? new DateTimeImmutable($data['last_used_at']) : null,
            createdAt: isset($data['created_at']) ? new DateTimeImmutable($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? new DateTimeImmutable($data['updated_at']) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'name' => $this->name,
            'description' => $this->description,
            'base_template' => $this->baseTemplate,
            'primary_color' => $this->primaryColor,
            'secondary_color' => $this->secondaryColor,
            'accent_color' => $this->accentColor,
            'font_family' => $this->fontFamily,
            'heading_font' => $this->headingFont,
            'header_style' => $this->headerStyle,
            'layout_style' => $this->layoutStyle,
            'show_logo' => $this->showLogo,
            'show_business_details' => $this->showBusinessDetails,
            'logo_position' => $this->logoPosition,
            'logo_size' => $this->logoSize,
            'border_radius' => $this->borderRadius,
            'border_style' => $this->borderStyle,
            'spacing' => $this->spacing,
            'table_style' => $this->tableStyle,
            'custom_css' => $this->customCss,
            'section_visibility' => $this->sectionVisibility,
            'field_labels' => $this->fieldLabels,
            'layout_json' => $this->layoutJson,
            'field_config' => $this->fieldConfig,
            'logo_url' => $this->logoUrl,
            'version' => $this->version,
            'category' => $this->category,
            'tags' => $this->tags,
            'is_public' => $this->isPublic,
            'usage_count' => $this->usageCount,
            'last_used_at' => $this->lastUsedAt?->format('Y-m-d H:i:s'),
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}