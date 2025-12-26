<?php

declare(strict_types=1);

namespace App\Application\GrowBuilder\DTOs;

final class CreateSiteDTO
{
    public function __construct(
        public readonly int $userId,
        public readonly string $name,
        public readonly string $subdomain,
        public readonly ?int $templateId = null,
        public readonly ?string $description = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            userId: $data['user_id'],
            name: $data['name'],
            subdomain: $data['subdomain'],
            templateId: $data['template_id'] ?? null,
            description: $data['description'] ?? null,
        );
    }
}
