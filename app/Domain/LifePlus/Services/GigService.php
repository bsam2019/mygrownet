<?php

namespace App\Domain\LifePlus\Services;

use App\Infrastructure\Persistence\Eloquent\LifePlusGigModel;
use App\Infrastructure\Persistence\Eloquent\LifePlusGigApplicationModel;
use Carbon\Carbon;

class GigService
{
    public function getGigs(array $filters = []): array
    {
        $query = LifePlusGigModel::with(['poster', 'assignee']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        } else {
            $query->where('status', 'open');
        }

        if (!empty($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        if (!empty($filters['location'])) {
            $query->where('location', 'like', '%' . $filters['location'] . '%');
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Location-based filtering with coordinates
        if (!empty($filters['latitude']) && !empty($filters['longitude']) && !empty($filters['radius'])) {
            $lat = (float) $filters['latitude'];
            $lng = (float) $filters['longitude'];
            $radius = (float) $filters['radius']; // in kilometers

            // Haversine formula for distance calculation
            $query->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->selectRaw("*, (
                    6371 * acos(
                        cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) +
                        sin(radians(?)) * sin(radians(latitude))
                    )
                ) AS distance", [$lat, $lng, $lat])
                ->having('distance', '<=', $radius)
                ->orderBy('distance');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        return $query->limit($filters['limit'] ?? 50)
            ->get()
            ->map(fn($g) => $this->mapGig($g))
            ->toArray();
    }

    /**
     * Get gigs near a location
     */
    public function getNearbyGigs(float $latitude, float $longitude, float $radiusKm = 10): array
    {
        return $this->getGigs([
            'latitude' => $latitude,
            'longitude' => $longitude,
            'radius' => $radiusKm,
            'status' => 'open',
        ]);
    }

    public function getGig(int $id): ?array
    {
        $gig = LifePlusGigModel::with(['poster', 'assignee', 'applications.applicant'])
            ->find($id);

        return $gig ? $this->mapGig($gig, true) : null;
    }

    public function getMyGigs(int $userId): array
    {
        return LifePlusGigModel::where('user_id', $userId)
            ->with(['assignee'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($g) => $this->mapGig($g))
            ->toArray();
    }

    public function getMyApplications(int $userId): array
    {
        return LifePlusGigApplicationModel::where('user_id', $userId)
            ->with(['gig.poster'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($a) => [
                'id' => $a->id,
                'gig' => $this->mapGig($a->gig),
                'message' => $a->message,
                'status' => $a->status,
                'applied_at' => $a->created_at->format('M d, Y'),
            ])
            ->toArray();
    }

    public function createGig(int $userId, array $data): array
    {
        $gig = LifePlusGigModel::create([
            'user_id' => $userId,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'category' => $data['category'] ?? null,
            'payment_amount' => $data['payment_amount'] ?? null,
            'location' => $data['location'] ?? null,
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
            'status' => 'open',
        ]);

        return $this->mapGig($gig->load('poster'));
    }

    public function updateGig(int $id, int $userId, array $data): ?array
    {
        $gig = LifePlusGigModel::where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if (!$gig) return null;

        $gig->update($data);
        return $this->mapGig($gig->fresh(['poster', 'assignee']));
    }

    public function deleteGig(int $id, int $userId): bool
    {
        return LifePlusGigModel::where('id', $id)
            ->where('user_id', $userId)
            ->delete() > 0;
    }

    public function applyForGig(int $gigId, int $userId, ?string $message = null): ?array
    {
        $gig = LifePlusGigModel::find($gigId);
        if (!$gig || $gig->status !== 'open' || $gig->user_id === $userId) {
            return null;
        }

        // Check if already applied
        $existing = LifePlusGigApplicationModel::where('gig_id', $gigId)
            ->where('user_id', $userId)
            ->first();

        if ($existing) return null;

        $application = LifePlusGigApplicationModel::create([
            'gig_id' => $gigId,
            'user_id' => $userId,
            'message' => $message,
            'status' => 'pending',
        ]);

        return [
            'id' => $application->id,
            'status' => 'pending',
            'message' => 'Application submitted successfully',
        ];
    }

    public function assignGig(int $gigId, int $ownerId, int $workerId): ?array
    {
        $gig = LifePlusGigModel::where('id', $gigId)
            ->where('user_id', $ownerId)
            ->where('status', 'open')
            ->first();

        if (!$gig) return null;

        $gig->update([
            'assigned_to' => $workerId,
            'status' => 'assigned',
        ]);

        // Update application status
        LifePlusGigApplicationModel::where('gig_id', $gigId)
            ->where('user_id', $workerId)
            ->update(['status' => 'accepted']);

        // Reject other applications
        LifePlusGigApplicationModel::where('gig_id', $gigId)
            ->where('user_id', '!=', $workerId)
            ->update(['status' => 'rejected']);

        return $this->mapGig($gig->fresh(['poster', 'assignee']));
    }

    public function completeGig(int $gigId, int $userId): ?array
    {
        $gig = LifePlusGigModel::where('id', $gigId)
            ->where(function ($q) use ($userId) {
                $q->where('user_id', $userId)
                  ->orWhere('assigned_to', $userId);
            })
            ->where('status', 'assigned')
            ->first();

        if (!$gig) return null;

        $gig->update(['status' => 'completed']);
        return $this->mapGig($gig->fresh(['poster', 'assignee']));
    }

    public function getCategories(): array
    {
        return [
            ['id' => 'cleaning', 'name' => 'Cleaning', 'icon' => '🧹'],
            ['id' => 'yard_work', 'name' => 'Yard Work', 'icon' => '🌿'],
            ['id' => 'cooking', 'name' => 'Cooking', 'icon' => '🍳'],
            ['id' => 'babysitting', 'name' => 'Babysitting', 'icon' => '👶'],
            ['id' => 'tutoring', 'name' => 'Tutoring', 'icon' => '📚'],
            ['id' => 'delivery', 'name' => 'Delivery', 'icon' => '📦'],
            ['id' => 'repairs', 'name' => 'Repairs', 'icon' => '🔧'],
            ['id' => 'errands', 'name' => 'Errands', 'icon' => '🏃'],
            ['id' => 'other', 'name' => 'Other', 'icon' => '💼'],
        ];
    }

    private function mapGig($gig, bool $includeApplications = false): array
    {
        $currentUserId = auth()->id();
        
        $data = [
            'id' => $gig->id,
            'title' => $gig->title,
            'description' => $gig->description,
            'category' => $gig->category,
            'category_icon' => collect($this->getCategories())
                ->firstWhere('id', $gig->category)['icon'] ?? '💼',
            'payment_amount' => $gig->payment_amount ? (float) $gig->payment_amount : null,
            'formatted_payment' => $gig->payment_amount ? 'K ' . number_format($gig->payment_amount) : 'Negotiable',
            'location' => $gig->location,
            'status' => $gig->status,
            'status_color' => match($gig->status) {
                'open' => '#10b981',
                'assigned' => '#f59e0b',
                'completed' => '#3b82f6',
                'cancelled' => '#ef4444',
                default => '#6b7280',
            },
            'poster' => $gig->poster ? [
                'id' => $gig->poster->id,
                'name' => $gig->poster->name,
            ] : null,
            'assigned_to' => $gig->assignee ? [
                'id' => $gig->assignee->id,
                'name' => $gig->assignee->name,
            ] : null,
            'posted_at' => $gig->created_at->diffForHumans(),
            'created_at' => $gig->created_at->toISOString(),
            'is_owner' => $currentUserId && $gig->user_id === $currentUserId,
            'has_applied' => false,
        ];

        if ($includeApplications && $gig->relationLoaded('applications')) {
            $data['applications'] = $gig->applications->map(fn($a) => [
                'id' => $a->id,
                'user' => [
                    'id' => $a->user_id,
                    'name' => $a->applicant?->name,
                ],
                'message' => $a->message,
                'status' => $a->status,
                'applied_at' => $a->created_at->format('M d, Y'),
            ])->toArray();
            
            // Check if current user has applied
            if ($currentUserId) {
                $data['has_applied'] = $gig->applications->contains('user_id', $currentUserId);
            }
        }

        return $data;
    }
}
