<?php

declare(strict_types=1);

namespace App\Domain\GrowStream\Services;

use App\Domain\GrowStream\Repositories\CreatorEarningRepositoryInterface;
use App\Domain\GrowStream\Repositories\CreatorPayoutRepositoryInterface;
use App\Domain\GrowStream\Repositories\CreatorProfileRepositoryInterface;
use Illuminate\Support\Facades\DB;

/**
 * Generates payout records for creators whose pending earnings meet the
 * minimum payout threshold.
 */
class PayoutService
{
    public function __construct(
        private CreatorEarningRepositoryInterface $earningRepo,
        private CreatorPayoutRepositoryInterface $payoutRepo,
        private CreatorProfileRepositoryInterface $creatorRepo,
    ) {}

    /**
     * Create payout records for all creators with eligible pending earnings.
     *
     * @return array<int, array<string, mixed>>
     */
    public function processEligible(string $referencePrefix = 'PAY'): array
    {
        $minimumPayout = (float) config('growstream.creator.minimum_payout', 100);

        $pendingEarnings = $this->earningRepo->allPending()
            ->groupBy('creator_id');

        $created = [];

        foreach ($pendingEarnings as $creatorId => $earnings) {
            $total = (float) $earnings->sum('earned_amount');

            if ($total < $minimumPayout) {
                continue;
            }

            $creator = $this->creatorRepo->findById((int) $creatorId);
            if ($creator === null) {
                continue;
            }

            $periodStart = $earnings->min('period_start');
            $periodEnd = $earnings->max('period_end');

            DB::transaction(function () use ($creatorId, $earnings, $total, $periodStart, $periodEnd, $referencePrefix, &$created) {
                $payout = $this->payoutRepo->create([
                    'creator_id' => $creatorId,
                    'amount' => $total,
                    'status' => 'pending',
                    'reference' => $referencePrefix.'-'.strtoupper(substr(md5(uniqid((string) $creatorId, true)), 0, 10)),
                    'period_start' => $periodStart?->format('Y-m-d'),
                    'period_end' => $periodEnd?->format('Y-m-d'),
                ]);

                foreach ($earnings as $earning) {
                    $earning->update(['status' => 'paid']);
                }

                $created[] = $payout->toArray();
            });
        }

        return $created;
    }

    /**
     * Mark a payout as processed/completed.
     */
    public function markCompleted(int $payoutId, string $reference, ?\DateTimeInterface $paidAt = null): array
    {
        $payout = $this->payoutRepo->findById($payoutId);
        if ($payout === null) {
            throw new \RuntimeException("Payout not found: {$payoutId}");
        }

        $this->payoutRepo->update($payout, [
            'status' => 'completed',
            'reference' => $reference,
            'paid_at' => $paidAt ?? now(),
        ]);

        return $payout->fresh()->toArray();
    }

    /**
     * Mark a payout as failed.
     */
    public function markFailed(int $payoutId, string $reason): array
    {
        $payout = $this->payoutRepo->findById($payoutId);
        if ($payout === null) {
            throw new \RuntimeException("Payout not found: {$payoutId}");
        }

        $this->payoutRepo->update($payout, [
            'status' => 'failed',
            'notes' => $reason,
        ]);

        return $payout->fresh()->toArray();
    }
}
