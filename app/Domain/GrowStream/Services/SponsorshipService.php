<?php

declare(strict_types=1);

namespace App\Domain\GrowStream\Services;

use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\SponsorshipGrant;
use App\Domain\GrowStream\Repositories\CreatorProfileRepositoryInterface;
use App\Domain\GrowStream\Repositories\SponsorshipGrantRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

/**
 * GrowStream creator sponsorship fund lifecycle:
 *   submitted → approved | rejected → disbursed → completed
 */
class SponsorshipService
{
    public function __construct(
        private SponsorshipGrantRepositoryInterface $grantRepo,
        private CreatorProfileRepositoryInterface $creatorRepo,
    ) {}

    public function apply(int $creatorId, string $title, string $description, float $amount, array $milestones = []): array
    {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Grant amount must be greater than zero');
        }

        $grant = $this->grantRepo->create([
            'creator_id' => $creatorId,
            'title' => $title,
            'description' => $description,
            'amount' => $amount,
            'currency' => 'ZMW',
            'milestones' => $milestones,
            'status' => 'submitted',
        ]);

        return $grant->toArray();
    }

    public function approve(int $grantId, ?int $reviewedBy = null): array
    {
        $grant = $this->findOrFail($grantId);

        $this->grantRepo->update($grant, [
            'status' => 'approved',
            'reviewed_by' => $reviewedBy,
            'rejection_reason' => null,
            'allocated_at' => now(),
        ]);

        return $grant->fresh()->toArray();
    }

    public function reject(int $grantId, string $reason, ?int $reviewedBy = null): array
    {
        $grant = $this->findOrFail($grantId);

        $this->grantRepo->update($grant, [
            'status' => 'rejected',
            'reviewed_by' => $reviewedBy,
            'rejection_reason' => $reason,
        ]);

        return $grant->fresh()->toArray();
    }

    public function disburse(int $grantId): array
    {
        $grant = $this->findOrFail($grantId);

        if ($grant->status !== 'approved') {
            throw new \RuntimeException('Only approved grants can be disbursed');
        }

        // Reflect the grant in the creator's pending payout ledger.
        $creator = $this->creatorRepo->findById($grant->creator_id);
        if ($creator !== null) {
            $this->creatorRepo->update($creator, [
                'pending_payout' => (float) $creator->pending_payout + (float) $grant->amount,
            ]);
        }

        $this->grantRepo->update($grant, [
            'status' => 'disbursed',
            'disbursed_at' => now(),
        ]);

        return $grant->fresh()->toArray();
    }

    public function complete(int $grantId): array
    {
        $grant = $this->findOrFail($grantId);

        $this->grantRepo->update($grant, [
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        return $grant->fresh()->toArray();
    }

    public function forCreator(int $creatorId, array $relations = []): Collection
    {
        return $this->grantRepo->forCreator($creatorId, $relations);
    }

    public function paginate(array $filters = [], int $perPage = 20)
    {
        return $this->grantRepo->paginate($filters, $perPage, ['creator.user']);
    }

    public function totalApproved(): float
    {
        return $this->grantRepo->totalApproved();
    }

    public function totalDisbursed(): float
    {
        return $this->grantRepo->totalDisbursed();
    }

    protected function findOrFail(int $grantId): SponsorshipGrant
    {
        $grant = $this->grantRepo->findById($grantId);
        if ($grant === null) {
            throw new \RuntimeException("Sponsorship grant not found: {$grantId}");
        }

        return $grant;
    }
}
