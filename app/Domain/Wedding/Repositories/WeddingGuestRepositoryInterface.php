<?php

namespace App\Domain\Wedding\Repositories;

use App\Domain\Wedding\Entities\WeddingGuest;

interface WeddingGuestRepositoryInterface
{
    public function save(WeddingGuest $guest): WeddingGuest;

    public function findById(int $id): ?WeddingGuest;

    public function findByWeddingEventId(int $weddingEventId): array;

    public function findByName(int $weddingEventId, string $name): ?WeddingGuest;

    public function searchByName(int $weddingEventId, string $searchTerm): array;

    public function findByEmail(int $weddingEventId, string $email): ?WeddingGuest;

    public function update(WeddingGuest $guest): WeddingGuest;

    public function updateRsvpStatus(
        int $guestId,
        string $status,
        int $confirmedGuests,
        ?string $dietaryRestrictions = null,
        ?string $message = null,
        ?string $email = null
    ): WeddingGuest;

    public function delete(int $id): bool;

    public function getStats(int $weddingEventId): array;

    public function bulkImport(int $weddingEventId, array $guests): int;

    public function createPendingGuest(
        int $weddingEventId,
        string $name,
        ?string $phone = null,
        ?string $email = null,
        ?string $message = null,
        string $rsvpStatus = 'inquiry',
        int $guestCount = 1
    ): WeddingGuest;
}
