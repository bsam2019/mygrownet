<?php

namespace App\Domain\Wedding\Repositories;

use App\Domain\Wedding\Entities\WeddingRsvp;

interface WeddingRsvpRepositoryInterface
{
    public function save(WeddingRsvp $rsvp): WeddingRsvp;
    
    public function findById(int $id): ?WeddingRsvp;
    
    public function findByWeddingEventId(int $weddingEventId): array;
    
    public function findByEmail(int $weddingEventId, string $email): ?WeddingRsvp;
    
    public function findByName(int $weddingEventId, string $name): ?WeddingRsvp;
    
    public function getAttendingCount(int $weddingEventId): int;
    
    public function getDeclinedCount(int $weddingEventId): int;
    
    public function getTotalGuestCount(int $weddingEventId): int;
    
    public function getStats(int $weddingEventId): array;
    
    public function delete(int $id): bool;
    
    public function update(WeddingRsvp $rsvp): WeddingRsvp;
}
