<?php

namespace App\Domain\Wedding\Entities;

class WeddingTemplate
{
    private function __construct(
        private ?int $id,
        private string $name,
        private string $slug,
        private ?string $description,
        private ?string $previewImage,
        private array $settings,
        private bool $isActive,
        private bool $isPremium,
        private ?\DateTimeImmutable $createdAt = null,
        private ?\DateTimeImmutable $updatedAt = null
    ) {}

    public static function create(
        string $name,
        string $slug,
        array $settings,
        ?string $description = null,
        ?string $previewImage = null,
        bool $isActive = true,
        bool $isPremium = false
    ): self {
        return new self(
            id: null,
            name: $name,
            slug: $slug,
            description: $description,
            previewImage: $previewImage,
            settings: $settings,
            isActive: $isActive,
            isPremium: $isPremium
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            name: $data['name'],
            slug: $data['slug'],
            description: $data['description'] ?? null,
            previewImage: $data['preview_image'] ?? null,
            settings: is_string($data['settings'] ?? null) ? json_decode($data['settings'], true) : ($data['settings'] ?? []),
            isActive: $data['is_active'] ?? true,
            isPremium: $data['is_premium'] ?? false,
            createdAt: isset($data['created_at']) ? new \DateTimeImmutable($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? new \DateTimeImmutable($data['updated_at']) : null
        );
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getPreviewImage(): ?string
    {
        return $this->previewImage;
    }

    public function getSettings(): array
    {
        return $this->settings;
    }

    public function getColors(): array
    {
        return $this->settings['colors'] ?? [];
    }

    public function getFonts(): array
    {
        return $this->settings['fonts'] ?? [];
    }

    public function getLayout(): array
    {
        return $this->settings['layout'] ?? [];
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function isPremium(): bool
    {
        return $this->isPremium;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'preview_image' => $this->previewImage,
            'settings' => $this->settings,
            'is_active' => $this->isActive,
            'is_premium' => $this->isPremium,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
