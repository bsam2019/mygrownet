<?php

namespace App\Domain\BizBoost\Entities;

class Integration
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $businessId,
        public readonly string $provider,
        public readonly ?string $providerUserId,
        public readonly ?string $providerPageId,
        public readonly ?string $providerPageName,
        public readonly ?string $accessToken,
        public readonly ?string $refreshToken,
        public readonly ?string $tokenExpiresAt,
        public readonly ?array $scopes,
        public readonly ?array $meta,
        public readonly ?string $catalogId,
        public readonly ?array $whatsappCatalogSettings,
        public readonly string $status,
        public readonly ?string $connectedAt,
        public readonly ?string $lastUsedAt,
        public readonly ?string $createdAt,
        public readonly ?string $updatedAt,
    ) {}

    public static function reconstitute(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            businessId: (int) $data['business_id'],
            provider: $data['provider'],
            providerUserId: $data['provider_user_id'] ?? null,
            providerPageId: $data['provider_page_id'] ?? null,
            providerPageName: $data['provider_page_name'] ?? null,
            accessToken: $data['access_token'] ?? null,
            refreshToken: $data['refresh_token'] ?? null,
            tokenExpiresAt: $data['token_expires_at'] ?? null,
            scopes: $data['scopes'] ?? null,
            meta: $data['meta'] ?? null,
            catalogId: $data['catalog_id'] ?? null,
            whatsappCatalogSettings: $data['whatsapp_catalog_settings'] ?? null,
            status: $data['status'],
            connectedAt: $data['connected_at'] ?? null,
            lastUsedAt: $data['last_used_at'] ?? null,
            createdAt: $data['created_at'] ?? null,
            updatedAt: $data['updated_at'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'business_id' => $this->businessId,
            'provider' => $this->provider,
            'provider_user_id' => $this->providerUserId,
            'provider_page_id' => $this->providerPageId,
            'provider_page_name' => $this->providerPageName,
            'access_token' => $this->accessToken,
            'refresh_token' => $this->refreshToken,
            'token_expires_at' => $this->tokenExpiresAt,
            'scopes' => $this->scopes,
            'meta' => $this->meta,
            'catalog_id' => $this->catalogId,
            'whatsapp_catalog_settings' => $this->whatsappCatalogSettings,
            'status' => $this->status,
            'connected_at' => $this->connectedAt,
            'last_used_at' => $this->lastUsedAt,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}