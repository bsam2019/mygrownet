<?php

namespace App\Infrastructure\Persistence\Repositories\Wedding;

use App\Domain\Wedding\Entities\WeddingEvent;
use App\Domain\Wedding\Repositories\WeddingEventRepositoryInterface;
use App\Domain\Wedding\ValueObjects\WeddingBudget;
use App\Domain\Wedding\ValueObjects\WeddingStatus;
use App\Infrastructure\Persistence\Eloquent\Wedding\WeddingEventModel;
use Carbon\Carbon;

class EloquentWeddingEventRepository implements WeddingEventRepositoryInterface
{
    public function save(WeddingEvent $weddingEvent): WeddingEvent
    {
        $model = $weddingEvent->getId() 
            ? WeddingEventModel::find($weddingEvent->getId())
            : new WeddingEventModel();

        $model->fill([
            'user_id' => $weddingEvent->getUserId(),
            'partner_name' => $weddingEvent->getPartnerName(),
            'partner_email' => $weddingEvent->getPartnerEmail(),
            'partner_phone' => $weddingEvent->getPartnerPhone(),
            'wedding_date' => $weddingEvent->getWeddingDate(),
            'venue_name' => $weddingEvent->getVenueName(),
            'venue_location' => $weddingEvent->getVenueLocation(),
            'budget' => $weddingEvent->getBudget()->getAmount(),
            'guest_count' => $weddingEvent->getGuestCount(),
            'status' => $weddingEvent->getStatus()->getValue(),
            'notes' => $weddingEvent->getNotes(),
            'preferences' => $weddingEvent->getPreferences()
        ]);

        $model->save();

        return $this->toDomainEntity($model);
    }

    public function findById(int $id): ?WeddingEvent
    {
        $model = WeddingEventModel::find($id);
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findByUserId(int $userId): array
    {
        $models = WeddingEventModel::where('user_id', $userId)
            ->orderBy('wedding_date', 'desc')
            ->get();

        return $models->map(fn($model) => $this->toDomainEntity($model))->toArray();
    }

    public function findByStatus(WeddingStatus $status): array
    {
        $models = WeddingEventModel::byStatus($status->getValue())
            ->orderBy('wedding_date', 'asc')
            ->get();

        return $models->map(fn($model) => $this->toDomainEntity($model))->toArray();
    }

    public function findUpcomingEvents(int $limit = 10): array
    {
        $models = WeddingEventModel::upcoming()
            ->orderBy('wedding_date', 'asc')
            ->limit($limit)
            ->get();

        return $models->map(fn($model) => $this->toDomainEntity($model))->toArray();
    }

    public function findEventsByDateRange(Carbon $startDate, Carbon $endDate): array
    {
        $models = WeddingEventModel::whereBetween('wedding_date', [$startDate, $endDate])
            ->orderBy('wedding_date', 'asc')
            ->get();

        return $models->map(fn($model) => $this->toDomainEntity($model))->toArray();
    }

    public function findUserActiveEvent(int $userId): ?WeddingEvent
    {
        $model = WeddingEventModel::where('user_id', $userId)
            ->active()
            ->first();

        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findBySlug(string $slug): ?WeddingEvent
    {
        // For now, we'll treat slug as ID since we don't have slug field yet
        // In future, we can add a slug field to the wedding_events table
        if (is_numeric($slug)) {
            return $this->findById((int)$slug);
        }
        
        // Try to find by a generated slug pattern (e.g., "john-jane-2025")
        // This is a placeholder implementation
        return null;
    }

    public function delete(int $id): bool
    {
        return WeddingEventModel::destroy($id) > 0;
    }

    public function getEventStats(): array
    {
        return [
            'total_events' => WeddingEventModel::count(),
            'active_events' => WeddingEventModel::active()->count(),
            'upcoming_events' => WeddingEventModel::upcoming()->count(),
            'completed_events' => WeddingEventModel::byStatus('completed')->count(),
            'cancelled_events' => WeddingEventModel::byStatus('cancelled')->count(),
            'events_this_month' => WeddingEventModel::whereMonth('wedding_date', now()->month)
                ->whereYear('wedding_date', now()->year)
                ->count()
        ];
    }

    private function toDomainEntity(WeddingEventModel $model): WeddingEvent
    {
        return new WeddingEvent(
            id: $model->id,
            userId: $model->user_id,
            partnerName: $model->partner_name,
            partnerEmail: $model->partner_email,
            partnerPhone: $model->partner_phone,
            weddingDate: Carbon::parse($model->wedding_date),
            venueName: $model->venue_name,
            venueLocation: $model->venue_location,
            budget: WeddingBudget::fromAmount($model->budget),
            guestCount: $model->guest_count,
            status: WeddingStatus::fromString($model->status),
            notes: $model->notes,
            preferences: $model->preferences,
            createdAt: $model->created_at,
            updatedAt: $model->updated_at
        );
    }
}