<?php

namespace App\Domain\Support\Repositories;

use App\Domain\Support\Entities\Ticket;
use App\Domain\Support\ValueObjects\TicketId;
use App\Domain\Support\ValueObjects\UserId;
use App\Domain\Support\ValueObjects\TicketStatus;
use App\Domain\Support\ValueObjects\TicketCategory;
use App\Domain\Support\ValueObjects\TicketPriority;

interface TicketRepository
{
    public function save(Ticket $ticket): void;
    public function findById(TicketId $id): ?Ticket;
    public function findByUserId(UserId $userId): array;
    public function findByStatus(TicketStatus $status): array;
    public function findByAssignedTo(UserId $adminId): array;
    public function findAll(): array;
    public function findOverdue(int $hoursThreshold): array;
    public function nextId(): TicketId;
}
