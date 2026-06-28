<?php

namespace App\Domain\Support\Repositories;

use App\Domain\Support\Entities\TicketComment;
use App\Domain\Support\ValueObjects\TicketId;

interface TicketCommentRepository
{
    public function save(TicketComment $comment): void;
    public function findByTicketId(TicketId $ticketId, bool $includeInternal = false): array;
    public function nextId(): int;
}
