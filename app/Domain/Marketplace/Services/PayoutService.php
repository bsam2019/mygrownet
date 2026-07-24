<?php

namespace App\Domain\Marketplace\Services;

use App\Domain\Marketplace\Repositories\SellerRepositoryInterface;
use App\Domain\Marketplace\Repositories\PayoutRepositoryInterface;
use Illuminate\Support\Str;

class PayoutService
{
    public function __construct(
        private SellerRepositoryInterface $sellerRepository,
        private PayoutRepositoryInterface $payoutRepository,
    ) {}

    public function getAvailableBalance(int $sellerId): int
    {
        return $this->sellerRepository->getBalance($sellerId);
    }

    public function getMinimumPayoutAmount(): int
    {
        return config('marketplace.payouts.minimum_amount', 5000);
    }

    public function canRequestPayout(int $sellerId): array
    {
        $balance = $this->getAvailableBalance($sellerId);
        $minimum = $this->getMinimumPayoutAmount();

        $hasPendingPayout = $this->payoutRepository->hasPendingPayout($sellerId);

        if ($hasPendingPayout) {
            return [
                'can_request' => false,
                'reason' => 'You have a pending payout request. Please wait for it to be processed.',
            ];
        }

        if ($balance < $minimum) {
            return [
                'can_request' => false,
                'reason' => 'Minimum payout amount is K' . number_format($minimum / 100, 2) . '. Your current balance is K' . number_format($balance / 100, 2) . '.',
            ];
        }

        return [
            'can_request' => true,
            'available_balance' => $balance,
        ];
    }

    public function createPayoutRequest(int $sellerId, array $data): array
    {
        $check = $this->canRequestPayout($sellerId);
        if (!$check['can_request']) {
            throw new \Exception($check['reason']);
        }

        $amount = $data['amount'];
        $availableBalance = $this->getAvailableBalance($sellerId);

        if ($amount > $availableBalance) {
            throw new \Exception('Requested amount exceeds available balance.');
        }

        $commissionRate = 0;
        $commissionDeducted = (int) ($amount * $commissionRate);
        $netAmount = $amount - $commissionDeducted;

        $reference = 'PO-' . strtoupper(Str::random(10));

        $payout = $this->payoutRepository->create([
            'seller_id' => $sellerId,
            'amount' => $amount,
            'commission_deducted' => $commissionDeducted,
            'net_amount' => $netAmount,
            'payout_method' => $data['payout_method'],
            'account_number' => $data['account_number'],
            'account_name' => $data['account_name'],
            'bank_name' => $data['bank_name'] ?? null,
            'status' => 'pending',
            'reference' => $reference,
            'seller_notes' => $data['notes'] ?? null,
        ]);

        $this->sellerRepository->decrementBalance($sellerId, $amount);

        \Log::info('Payout request created', [
            'payout_id' => $payout['id'] ?? null,
            'seller_id' => $sellerId,
            'amount' => $amount,
            'reference' => $reference,
        ]);

        return $payout;
    }

    public function getSellerPayouts(int $sellerId, int $perPage = 20): array
    {
        return $this->payoutRepository->findBySeller($sellerId, $perPage);
    }

    public function getAllPayouts(array $filters = [], int $perPage = 20): array
    {
        return $this->payoutRepository->findAll($filters, $perPage);
    }

    public function approvePayout(int $payoutId, int $adminId, ?string $notes = null): array
    {
        $payout = $this->payoutRepository->findById($payoutId);
        if (!$payout || !in_array($payout['status'], ['pending'])) {
            throw new \Exception('This payout cannot be approved.');
        }

        return $this->payoutRepository->update($payoutId, [
            'status' => 'approved',
            'approved_by' => $adminId,
            'approved_at' => now(),
            'admin_notes' => $notes,
        ]);
    }

    public function rejectPayout(int $payoutId, int $adminId, string $reason): array
    {
        $payout = $this->payoutRepository->findById($payoutId);
        if (!$payout || !in_array($payout['status'], ['pending', 'approved'])) {
            throw new \Exception('This payout cannot be rejected.');
        }

        $result = $this->payoutRepository->update($payoutId, [
            'status' => 'rejected',
            'approved_by' => $adminId,
            'approved_at' => now(),
            'rejection_reason' => $reason,
        ]);

        $this->sellerRepository->incrementBalance($payout['seller_id'], $payout['amount']);

        return $result;
    }

    public function markAsProcessing(int $payoutId, int $adminId): array
    {
        $payout = $this->payoutRepository->findById($payoutId);
        if (!$payout || $payout['status'] !== 'approved') {
            throw new \Exception('This payout cannot be processed.');
        }

        return $this->payoutRepository->update($payoutId, [
            'status' => 'processing',
            'processed_by' => $adminId,
            'processed_at' => now(),
        ]);
    }

    public function completePayout(int $payoutId, string $transactionReference, ?array $metadata = null): array
    {
        $payout = $this->payoutRepository->findById($payoutId);
        if (!$payout || $payout['status'] !== 'processing') {
            throw new \Exception('Only processing payouts can be completed.');
        }

        return $this->payoutRepository->update($payoutId, [
            'status' => 'completed',
            'transaction_reference' => $transactionReference,
            'metadata' => $metadata,
        ]);
    }

    public function markAsFailed(int $payoutId, string $reason, ?array $metadata = null): array
    {
        $payout = $this->payoutRepository->findById($payoutId);
        if (!$payout || $payout['status'] !== 'processing') {
            throw new \Exception('Only processing payouts can be marked as failed.');
        }

        $result = $this->payoutRepository->update($payoutId, [
            'status' => 'failed',
            'rejection_reason' => $reason,
            'metadata' => $metadata,
        ]);

        $this->sellerRepository->incrementBalance($payout['seller_id'], $payout['amount']);

        return $result;
    }

    public function getPayoutStats(): array
    {
        return $this->payoutRepository->getStats();
    }
}
