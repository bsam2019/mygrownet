<?php

namespace App\Infrastructure\Persistence\Repositories\Wedding;

use App\Domain\Wedding\Entities\WeddingGuest;
use App\Domain\Wedding\Repositories\WeddingGuestRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Wedding\WeddingGuestModel;

class EloquentWeddingGuestRepository implements WeddingGuestRepositoryInterface
{
    public function save(WeddingGuest $guest): WeddingGuest
    {
        $model = WeddingGuestModel::create([
            'wedding_event_id' => $guest->getWeddingEventId(),
            'first_name' => $guest->getFirstName(),
            'last_name' => $guest->getLastName(),
            'email' => $guest->getEmail(),
            'phone' => $guest->getPhone(),
            'allowed_guests' => $guest->getAllowedGuests(),
            'group_name' => $guest->getGroupName(),
            'notes' => $guest->getNotes(),
            'invitation_sent' => $guest->isInvitationSent(),
        ]);

        return $this->toDomainEntity($model);
    }

    public function findById(int $id): ?WeddingGuest
    {
        $model = WeddingGuestModel::find($id);
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findByWeddingEventId(int $weddingEventId): array
    {
        return WeddingGuestModel::where('wedding_event_id', $weddingEventId)
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get()
            ->map(fn($model) => $this->toDomainEntity($model))
            ->toArray();
    }

    public function findByName(int $weddingEventId, string $name): ?WeddingGuest
    {
        $name = trim($name);
        $nameParts = explode(' ', $name, 2);
        $firstName = $nameParts[0] ?? '';
        $lastName = $nameParts[1] ?? '';

        $query = WeddingGuestModel::where('wedding_event_id', $weddingEventId);

        if ($lastName) {
            // Full name search - exact match
            $model = $query->whereRaw('LOWER(first_name) = ? AND LOWER(last_name) = ?', [
                strtolower($firstName),
                strtolower($lastName)
            ])->first();
        } else {
            // Single name - search both fields
            $model = $query->where(function ($q) use ($firstName) {
                $q->whereRaw('LOWER(first_name) = ?', [strtolower($firstName)])
                    ->orWhereRaw('LOWER(last_name) = ?', [strtolower($firstName)]);
            })->first();
        }

        return $model ? $this->toDomainEntity($model) : null;
    }

    public function searchByName(int $weddingEventId, string $searchTerm): array
    {
        $searchTerm = trim($searchTerm);
        $searchTermLower = strtolower($searchTerm);
        $nameParts = explode(' ', $searchTerm, 2);
        $firstName = $nameParts[0] ?? '';
        $lastName = $nameParts[1] ?? '';

        $query = WeddingGuestModel::where('wedding_event_id', $weddingEventId);

        // Use flexible OR-based search to find all potential matches
        $query->where(function ($q) use ($searchTermLower, $firstName, $lastName) {
            // Search 1: Full name concatenation (first + last)
            $q->whereRaw("LOWER(first_name || ' ' || last_name) LIKE ?", ['%' . $searchTermLower . '%']);
            
            // Search 2: Reverse name concatenation (last + first)
            $q->orWhereRaw("LOWER(last_name || ' ' || first_name) LIKE ?", ['%' . $searchTermLower . '%']);
            
            // Search 3: First name only
            $q->orWhereRaw('LOWER(first_name) LIKE ?', ['%' . strtolower($firstName) . '%']);
            
            // Search 4: Last name only (using first search term)
            $q->orWhereRaw('LOWER(last_name) LIKE ?', ['%' . strtolower($firstName) . '%']);
            
            // Search 5: If last name provided, also search it specifically
            if ($lastName) {
                $q->orWhereRaw('LOWER(last_name) LIKE ?', ['%' . strtolower($lastName) . '%']);
            }
        });

        return $query->orderByRaw("
            CASE 
                WHEN LOWER(first_name || ' ' || last_name) = ? THEN 1
                WHEN LOWER(first_name) = ? THEN 2
                WHEN LOWER(first_name || ' ' || last_name) LIKE ? THEN 3
                ELSE 4
            END
        ", [$searchTermLower, strtolower($firstName), $searchTermLower . '%'])
            ->limit(10)
            ->get()
            ->map(fn($model) => $this->toDomainEntity($model))
            ->toArray();
    }

    public function findByEmail(int $weddingEventId, string $email): ?WeddingGuest
    {
        $model = WeddingGuestModel::where('wedding_event_id', $weddingEventId)
            ->whereRaw('LOWER(email) = ?', [strtolower($email)])
            ->first();

        return $model ? $this->toDomainEntity($model) : null;
    }

    public function update(WeddingGuest $guest): WeddingGuest
    {
        $model = WeddingGuestModel::find($guest->getId());

        if (!$model) {
            throw new \Exception('Guest not found');
        }

        $model->update([
            'first_name' => $guest->getFirstName(),
            'last_name' => $guest->getLastName(),
            'email' => $guest->getEmail(),
            'phone' => $guest->getPhone(),
            'allowed_guests' => $guest->getAllowedGuests(),
            'group_name' => $guest->getGroupName(),
            'notes' => $guest->getNotes(),
            'invitation_sent' => $guest->isInvitationSent(),
            'rsvp_status' => $guest->getRsvpStatus(),
            'confirmed_guests' => $guest->getConfirmedGuests(),
            'dietary_restrictions' => $guest->getDietaryRestrictions(),
            'rsvp_message' => $guest->getRsvpMessage(),
            'rsvp_submitted_at' => $guest->getRsvpSubmittedAt(),
        ]);

        return $this->toDomainEntity($model->fresh());
    }

    public function delete(int $id): bool
    {
        return WeddingGuestModel::destroy($id) > 0;
    }

    public function updateRsvpStatus(
        int $guestId,
        string $status,
        int $confirmedGuests,
        ?string $dietaryRestrictions = null,
        ?string $message = null,
        ?string $email = null
    ): WeddingGuest {
        $model = WeddingGuestModel::find($guestId);

        if (!$model) {
            throw new \Exception('Guest not found');
        }

        $updateData = [
            'rsvp_status' => $status,
            'confirmed_guests' => $confirmedGuests,
            'dietary_restrictions' => $dietaryRestrictions,
            'rsvp_message' => $message,
            'rsvp_submitted_at' => now(),
        ];

        // Only update email if provided (don't overwrite existing with null)
        if ($email) {
            $updateData['email'] = $email;
        }

        $model->update($updateData);

        return $this->toDomainEntity($model->fresh());
    }

    public function getStats(int $weddingEventId): array
    {
        $total = WeddingGuestModel::where('wedding_event_id', $weddingEventId)->count();
        $totalAllowedGuests = WeddingGuestModel::where('wedding_event_id', $weddingEventId)
            ->sum('allowed_guests');
        $invitationsSent = WeddingGuestModel::where('wedding_event_id', $weddingEventId)
            ->where('invitation_sent', true)
            ->count();
        
        // RSVP stats
        $attending = WeddingGuestModel::where('wedding_event_id', $weddingEventId)
            ->where('rsvp_status', 'attending')
            ->count();
        $declined = WeddingGuestModel::where('wedding_event_id', $weddingEventId)
            ->where('rsvp_status', 'declined')
            ->count();
        $pending = WeddingGuestModel::where('wedding_event_id', $weddingEventId)
            ->where('rsvp_status', 'pending')
            ->count();
        $totalConfirmedGuests = WeddingGuestModel::where('wedding_event_id', $weddingEventId)
            ->where('rsvp_status', 'attending')
            ->sum('confirmed_guests');

        return [
            'total_invited' => $total,
            'total_allowed_guests' => $totalAllowedGuests,
            'invitations_sent' => $invitationsSent,
            'invitations_pending' => $total - $invitationsSent,
            'attending' => $attending,
            'declined' => $declined,
            'pending' => $pending,
            'total_confirmed_guests' => $totalConfirmedGuests,
        ];
    }

    public function bulkImport(int $weddingEventId, array $guests): int
    {
        $count = 0;
        foreach ($guests as $guestData) {
            WeddingGuestModel::create([
                'wedding_event_id' => $weddingEventId,
                'first_name' => $guestData['first_name'],
                'last_name' => $guestData['last_name'],
                'email' => $guestData['email'] ?? null,
                'phone' => $guestData['phone'] ?? null,
                'allowed_guests' => $guestData['allowed_guests'] ?? 1,
                'group_name' => $guestData['group_name'] ?? null,
                'notes' => $guestData['notes'] ?? null,
                'invitation_sent' => false,
            ]);
            $count++;
        }
        return $count;
    }

    private function toDomainEntity(WeddingGuestModel $model): WeddingGuest
    {
        return WeddingGuest::fromArray([
            'id' => $model->id,
            'wedding_event_id' => $model->wedding_event_id,
            'first_name' => $model->first_name,
            'last_name' => $model->last_name,
            'email' => $model->email,
            'phone' => $model->phone,
            'allowed_guests' => $model->allowed_guests,
            'group_name' => $model->group_name,
            'notes' => $model->notes,
            'invitation_sent' => $model->invitation_sent,
            'rsvp_status' => $model->rsvp_status ?? 'pending',
            'confirmed_guests' => $model->confirmed_guests ?? 0,
            'dietary_restrictions' => $model->dietary_restrictions,
            'rsvp_message' => $model->rsvp_message,
            'rsvp_submitted_at' => $model->rsvp_submitted_at?->format('Y-m-d H:i:s'),
        ]);
    }
}
