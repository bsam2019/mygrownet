<?php

namespace App\Domain\LifePlus\Services;

use App\Domain\LifePlus\Entities\LifePlusGig;
use App\Domain\LifePlus\Repositories\GigRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\LifePlusGigApplicationModel;

class GigService
{
    public function __construct(
        private readonly GigRepositoryInterface $gigRepo,
    ) {}

    public function getGigs(array $filters = []): array
    {
        return array_map(fn($g) => $this->mapGig($g), $this->gigRepo->findOpen($filters));
    }

    public function getGig(int $id): ?array
    {
        $gig = $this->gigRepo->findById($id);
        return $gig ? $this->mapGig($gig) : null;
    }

    public function getMyGigs(int $userId): array
    {
        return array_map(fn($g) => $this->mapGig($g), $this->gigRepo->findByUser($userId));
    }

    public function createGig(int $userId, array $data): array
    {
        $gig = LifePlusGig::reconstitute([
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

        return $this->mapGig($this->gigRepo->save($gig));
    }

    public function updateGig(int $id, int $userId, array $data): ?array
    {
        $gig = $this->gigRepo->findById($id);
        if (!$gig || $gig->userId !== $userId) return null;

        $merged = array_merge($gig->toArray(), $data);
        return $this->mapGig($this->gigRepo->save(LifePlusGig::reconstitute($merged)));
    }

    public function deleteGig(int $id, int $userId): bool
    {
        $gig = $this->gigRepo->findById($id);
        if (!$gig || $gig->userId !== $userId) return false;
        return $this->gigRepo->delete($id);
    }

    public function applyForGig(int $gigId, int $userId, ?string $message = null): ?array
    {
        $gig = $this->gigRepo->findById($gigId);
        if (!$gig || $gig->status !== 'open' || $gig->userId === $userId) return null;

        $existing = LifePlusGigApplicationModel::where('gig_id', $gigId)
            ->where('user_id', $userId)
            ->first();

        if ($existing) return null;

        LifePlusGigApplicationModel::create([
            'gig_id' => $gigId,
            'user_id' => $userId,
            'message' => $message,
            'status' => 'pending',
        ]);

        return ['id' => 0, 'status' => 'pending', 'message' => 'Application submitted successfully'];
    }

    public function assignGig(int $gigId, int $ownerId, int $workerId): ?array
    {
        $gig = $this->gigRepo->findById($gigId);
        if (!$gig || $gig->userId !== $ownerId || $gig->status !== 'open') return null;

        $merged = $gig->toArray();
        $merged['assigned_to'] = $workerId;
        $merged['status'] = 'assigned';
        $saved = $this->gigRepo->save(LifePlusGig::reconstitute($merged));

        LifePlusGigApplicationModel::where('gig_id', $gigId)
            ->where('user_id', $workerId)
            ->update(['status' => 'accepted']);

        LifePlusGigApplicationModel::where('gig_id', $gigId)
            ->where('user_id', '!=', $workerId)
            ->update(['status' => 'rejected']);

        return $this->mapGig($saved);
    }

    public function completeGig(int $gigId, int $userId): ?array
    {
        $gig = $this->gigRepo->findById($gigId);
        if (!$gig || ($gig->userId !== $userId && $gig->assignedTo !== $userId)) return null;
        if ($gig->status !== 'assigned') return null;

        $merged = $gig->toArray();
        $merged['status'] = 'completed';
        return $this->mapGig($this->gigRepo->save(LifePlusGig::reconstitute($merged)));
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

    private function mapGig(LifePlusGig $gig): array
    {
        return [
            'id' => $gig->id,
            'title' => $gig->title,
            'description' => $gig->description,
            'category' => $gig->category,
            'payment_amount' => $gig->paymentAmount,
            'location' => $gig->location,
            'status' => $gig->status,
        ];
    }
}
