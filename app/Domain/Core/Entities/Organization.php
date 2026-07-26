<?php

namespace App\Domain\Core\Entities;

use DateTimeImmutable;

class Organization
{
    public function __construct(
        public readonly string $id,
        public readonly string $uuid,
        public readonly string $name,
        public readonly string $slug,
        public readonly string $type,
        public readonly string $status,
        public readonly string $ownerId,
        public readonly ?string $country,
        public readonly ?string $currency,
        public readonly ?string $timezone,
        public readonly ?string $language,
        public readonly array $settings,
        public readonly DateTimeImmutable $createdAt,
        public readonly ?DateTimeImmutable $updatedAt,
    ) {}
}
