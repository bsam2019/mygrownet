<?php

namespace App\Domain\BizBoost\Entities;

class QrCode
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $businessId,
        public readonly string $name,
        public readonly string $type,
        public readonly string $targetUrl,
        public readonly string $shortCode,
        public readonly ?string $qrImagePath,
        public readonly ?array $styleConfig,
        public readonly int $scanCount,
        public readonly bool $isActive,
        public readonly ?string $createdAt,
        public readonly ?string $updatedAt,
    ) {}

    public static function reconstitute(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            businessId: (int) $data['business_id'],
            name: $data['name'],
            type: $data['type'],
            targetUrl: $data['target_url'],
            shortCode: $data['short_code'],
            qrImagePath: $data['qr_image_path'] ?? null,
            styleConfig: $data['style_config'] ?? null,
            scanCount: (int) ($data['scan_count'] ?? 0),
            isActive: (bool) ($data['is_active'] ?? true),
            createdAt: $data['created_at'] ?? null,
            updatedAt: $data['updated_at'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'business_id' => $this->businessId,
            'name' => $this->name,
            'type' => $this->type,
            'target_url' => $this->targetUrl,
            'short_code' => $this->shortCode,
            'qr_image_path' => $this->qrImagePath,
            'style_config' => $this->styleConfig,
            'scan_count' => $this->scanCount,
            'is_active' => $this->isActive,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}