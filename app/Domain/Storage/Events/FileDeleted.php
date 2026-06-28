<?php

namespace App\Domain\Storage\Events;

class FileDeleted
{
    public function __construct(
        public readonly string $fileId,
        public readonly int $userId,
        public readonly int $sizeBytes,
        public readonly \DateTimeImmutable $occurredAt
    ) {}
}
