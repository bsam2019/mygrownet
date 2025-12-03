<?php

namespace App\Application\Messaging\DTOs;

class SendMessageDTO
{
    public function __construct(
        public readonly int $senderId,
        public readonly int $recipientId,
        public readonly string $subject,
        public readonly string $body,
        public readonly ?int $parentId = null,
        public readonly string $module = 'mygrownet',
        public readonly ?array $metadata = null
    ) {}
}
