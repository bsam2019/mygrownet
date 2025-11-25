<?php

namespace App\Infrastructure\Persistence\Repositories\Wedding;

use App\Domain\Wedding\Entities\WeddingRsvp;
use App\Domain\Wedding\Repositories\WeddingRsvpRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Wedding\WeddingRsvpModel;

class EloquentWeddingRsvpRepository implements WeddingRsvpRepositoryInterface
{
    public function save(WeddingRsvp $rsvp): WeddingRsvp
    {
        $model = WeddingRsvpModel::create([
            'wedding_event_id' => $rsvp->getWeddingEventId(),
            'first_name' => $rsvp->getFirstName(),
            'last_name' => $rsvp->getLastName(),
            'email' => $rsvp->getEmail(),
            'phone' => $rsvp->getPhone(),
            'attending' => $rsvp->isAttending(),
            'guest_count' => $rsvp->getGuestCount(),
            'dietary_restrictions' => $rsvp->getDietaryRestrictions(),
            'message' => $rsvp->getMessage(),
            'submitted_at' => $rsvp->getSubmittedAt()->format('Y-m-d H:i:s'),
        ]);

        return $this->toDomainEntity($model);
    }

    public function findById(int $id): ?WeddingRsvp
    {
        $model = WeddingRsvpModel::find($id);
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findByWeddingEventId(int $weddingEventId): array
    {
        return WeddingRsvpModel::where('wedding_event_id', $weddingEventId)
            ->orderBy('submitted_at', 'desc')
            ->get()
            ->map(fn($model) => $this->toDomainEntity($model))
            ->toArray();
    }

    public function findByEmail(int $weddingEventId, string $email): ?WeddingRsvp
    {
        $model = WeddingRsvpModel::where('wedding_event_id', $weddingEventId)
            ->where('email', $email)
            ->first();
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findByName(int $weddingEventId, string $name): ?WeddingRsvp
    {
        $nameParts = explode(' ', trim($name), 2);
        $firstName = $nameParts[0] ?? '';
        $lastName = $nameParts[1] ?? '';

        $query = WeddingRsvpModel::where('wedding_event_id', $weddingEventId);
        
        if ($lastName) {
            $query->where('first_name', 'LIKE', "%{$firstName}%")
                  ->where('last_name', 'LIKE', "%{$lastName}%");
        } else {
            $query->where(function($q) use ($firstName) {
                $q->where('first_name', 'LIKE', "%{$firstName}%")
                  ->orWhere('last_name', 'LIKE', "%{$firstName}%");
            });
        }

        $model = $query->first();
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function getAttendingCount(int $weddingEventId): int
    {
        return WeddingRsvpModel::where('wedding_event_id', $weddingEventId)
            ->where('attending', true)
            ->count();
    }

    public function getDeclinedCount(int $weddingEventId): int
    {
        return WeddingRsvpModel::where('wedding_event_id', $weddingEventId)
            ->where('attending', false)
            ->count();
    }

    public function getTotalGuestCount(int $weddingEventId): int
    {
        return WeddingRsvpModel::where('wedding_event_id', $weddingEventId)
            ->where('attending', true)
            ->sum('guest_count');
    }

    public function getStats(int $weddingEventId): array
    {
        $total = WeddingRsvpModel::where('wedding_event_id', $weddingEventId)->count();
        $attending = $this->getAttendingCount($weddingEventId);
        $declined = $this->getDeclinedCount($weddingEventId);
        $totalGuests = $this->getTotalGuestCount($weddingEventId);

        return [
            'total_responses' => $total,
            'attending' => $attending,
            'declined' => $declined,
            'total_guests' => $totalGuests,
            'pending' => 0, // Could be calculated if you have an invited list
        ];
    }

    public function delete(int $id): bool
    {
        return WeddingRsvpModel::destroy($id) > 0;
    }

    public function update(WeddingRsvp $rsvp): WeddingRsvp
    {
        $model = WeddingRsvpModel::find($rsvp->getId());
        
        if (!$model) {
            throw new \Exception('RSVP not found');
        }

        $model->update([
            'first_name' => $rsvp->getFirstName(),
            'last_name' => $rsvp->getLastName(),
            'email' => $rsvp->getEmail(),
            'phone' => $rsvp->getPhone(),
            'attending' => $rsvp->isAttending(),
            'guest_count' => $rsvp->getGuestCount(),
            'dietary_restrictions' => $rsvp->getDietaryRestrictions(),
            'message' => $rsvp->getMessage(),
        ]);

        return $this->toDomainEntity($model->fresh());
    }

    private function toDomainEntity(WeddingRsvpModel $model): WeddingRsvp
    {
        return WeddingRsvp::fromArray([
            'id' => $model->id,
            'wedding_event_id' => $model->wedding_event_id,
            'first_name' => $model->first_name,
            'last_name' => $model->last_name,
            'email' => $model->email,
            'phone' => $model->phone,
            'attending' => $model->attending,
            'guest_count' => $model->guest_count,
            'dietary_restrictions' => $model->dietary_restrictions,
            'message' => $model->message,
            'submitted_at' => $model->submitted_at?->format('Y-m-d H:i:s'),
        ]);
    }
}
