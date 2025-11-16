<?php

namespace App\Application\Support\DTOs;

class CreateTicketDTO
{
    public function __construct(
        public readonly int $userId,
        public readonly string $category,
        public readonly string $priority,
        public readonly string $subject,
        public readonly string $description
    ) {}
}
