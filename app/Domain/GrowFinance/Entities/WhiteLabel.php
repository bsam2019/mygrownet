<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\Entities;

class WhiteLabel
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $businessId,
        public readonly ?string $companyName,
        public readonly ?string $logoPath,
        public readonly ?string $faviconPath,
        public readonly ?string $primaryColor,
        public readonly ?string $secondaryColor,
        public readonly ?string $accentColor,
        public readonly ?string $customDomain,
        public readonly bool $hidePoweredBy,
        public readonly ?string $customCss,
        public readonly ?array $emailBranding,
        public readonly ?\DateTimeImmutable $createdAt,
        public readonly ?\DateTimeImmutable $updatedAt,
    ) {}

    public static function reconstitute(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            businessId: (int) $data['business_id'],
            companyName: $data['company_name'] ?? null,
            logoPath: $data['logo_path'] ?? null,
            faviconPath: $data['favicon_path'] ?? null,
            primaryColor: $data['primary_color'] ?? null,
            secondaryColor: $data['secondary_color'] ?? null,
            accentColor: $data['accent_color'] ?? null,
            customDomain: $data['custom_domain'] ?? null,
            hidePoweredBy: (bool) ($data['hide_powered_by'] ?? false),
            customCss: $data['custom_css'] ?? null,
            emailBranding: isset($data['email_branding']) ? (array) $data['email_branding'] : null,
            createdAt: isset($data['created_at']) ? new \DateTimeImmutable($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? new \DateTimeImmutable($data['updated_at']) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'business_id' => $this->businessId,
            'company_name' => $this->companyName,
            'logo_path' => $this->logoPath,
            'favicon_path' => $this->faviconPath,
            'primary_color' => $this->primaryColor,
            'secondary_color' => $this->secondaryColor,
            'accent_color' => $this->accentColor,
            'custom_domain' => $this->customDomain,
            'hide_powered_by' => $this->hidePoweredBy,
            'custom_css' => $this->customCss,
            'email_branding' => $this->emailBranding,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }

    public function getDisplayName(): string
    {
        return $this->companyName ?? 'My Business';
    }
}
