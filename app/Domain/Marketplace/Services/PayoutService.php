<?php

namespace App\Domain\Marketplace\Services;

use App\Models\MarketplacePayout;
use App\Models\MarketplaceSeller;
use Illuminate\Support\Str;

class PayoutService
{
    /**
     * Get seller's available balance for payout
     */
    public function getAvailableBalance(int $sellerId): int
    {
        $seller = MarketplaceSeller::findOrFail($sellerId);
        return $seller->available_balance ?? 0;
    }

    /**
     * Get minimum payout amount from config
     */
    public function getMinimumPayoutAmount(): int
    {
        return config('marketplace.payouts.minimum_amount', 5000); // K50 default
    }

    /**
     * Check if seller can request payout
     */
    public function canRequestPayout(int $sellerId): array
    {
        $balance = $this->getAvailableBalance($sellerId);
        $minimum = $this->getMinimumPayoutAmount();
        
        // Check for pending payouts
        $hasPendingPayout = MarketplacePayout::where('seller_id', $sellerId)
            ->whereIn('status', ['pending', 'approved', 'processing'])
            ->exists();
        
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

    /**
     * Create a payout request
     */
    public function createPayoutRequest(int $sellerId, array $data): MarketplacePayout
    {
        $seller = MarketplaceSeller::findOrFail($sellerId);
        
        // Validate balance
        $check = $this->canRequestPayout($sellerId);
        if (!$check['can_request']) {
            throw new \Exception($check['reason']);
        }
        
        $amount = $data['amount'];
        $availableBalance = $this->getAvailableBalance($sellerId);
        
        if ($amount > $availableBalance) {
            throw new \Exception('Requested amount exceeds available balance.');
        }
        
        // Calculate commission (if any additional commission on payouts)
        $commissionRate = 0; // No additional commission on payouts
        $commissionDeducted = (int) ($amount * $commissionRate);
        $netAmount = $amount - $commissionDeducted;
        
        // Generate unique reference
        $reference = 'PO-' . strtoupper(Str::random(10));
        
        // Create payout
        $payout = MarketplacePayout::create([
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
        
        // Deduct from seller's available balance
        $seller->decrement('available_balance', $amount);
        
        \Log::info('Payout request created', [
            'payout_id' => $payout->id,
            'seller_id' => $sellerId,
            'amount' => $amount,
            'reference' => $reference,
        ]);
        
        return $payout;
    }

    /**
     * Get seller's payout history
     */
    public function getSellerPayouts(int $sellerId, int $perPage = 20)
    {
        return MarketplacePayout::where('seller_id', $sellerId)
            ->with(['approvedBy', 'processedBy'])
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    /**
     * Get all payouts (admin)
     */
    public function getAllPayouts(array $filters = [], int $perPage = 20)
    {
        $query = MarketplacePayout::with(['seller.user', 'approvedBy', 'processedBy']);
        
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        if (!empty($filters['payout_method'])) {
            $query->where('payout_method', $filters['payout_method']);
        }
        
        if (!empty($filters['seller_id'])) {
            $query->where('seller_id', $filters['seller_id']);
        }
        
        return $query->orderByDesc('created_at')->paginate($perPage);
    }

    /**
     * Approve payout (admin)
     */
    public function approvePayout(int $payoutId, int $adminId, ?string $notes = null): MarketplacePayout
    {
        $payout = MarketplacePayout::findOrFail($payoutId);
        
        if (!$payout->canBeApproved()) {
            throw new \Exception('This payout cannot be approved.');
        }
        
        $payout->update([
            'status' => 'approved',
            'approved_by' => $adminId,
            'approved_at' => now(),
            'admin_notes' => $notes,
        ]);
        
        \Log::info('Payout approved', [
            'payout_id' => $payoutId,
            'admin_id' => $adminId,
        ]);
        
        return $payout;
    }

    /**
     * Reject payout (admin)
     */
    public function rejectPayout(int $payoutId, int $adminId, string $reason): MarketplacePayout
    {
        $payout = MarketplacePayout::findOrFail($payoutId);
        
        if (!$payout->canBeRejected()) {
            throw new \Exception('This payout cannot be rejected.');
        }
        
        $payout->update([
            'status' => 'rejected',
            'approved_by' => $adminId,
            'approved_at' => now(),
            'rejection_reason' => $reason,
        ]);
        
        // Refund amount to seller's available balance
        $payout->seller->increment('available_balance', $payout->amount);
        
        \Log::info('Payout rejected', [
            'payout_id' => $payoutId,
            'admin_id' => $adminId,
            'reason' => $reason,
        ]);
        
        return $payout;
    }

    /**
     * Mark payout as processing (admin)
     */
    public function markAsProcessing(int $payoutId, int $adminId): MarketplacePayout
    {
        $payout = MarketplacePayout::findOrFail($payoutId);
        
        if (!$payout->canBeProcessed()) {
            throw new \Exception('This payout cannot be processed.');
        }
        
        $payout->update([
            'status' => 'processing',
            'processed_by' => $adminId,
            'processed_at' => now(),
        ]);
        
        \Log::info('Payout marked as processing', [
            'payout_id' => $payoutId,
            'admin_id' => $adminId,
        ]);
        
        return $payout;
    }

    /**
     * Complete payout (admin)
     */
    public function completePayout(int $payoutId, string $transactionReference, ?array $metadata = null): MarketplacePayout
    {
        $payout = MarketplacePayout::findOrFail($payoutId);
        
        if ($payout->status !== 'processing') {
            throw new \Exception('Only processing payouts can be completed.');
        }
        
        $payout->update([
            'status' => 'completed',
            'transaction_reference' => $transactionReference,
            'metadata' => $metadata,
        ]);
        
        \Log::info('Payout completed', [
            'payout_id' => $payoutId,
            'transaction_reference' => $transactionReference,
        ]);
        
        return $payout;
    }

    /**
     * Mark payout as failed (admin)
     */
    public function markAsFailed(int $payoutId, string $reason, ?array $metadata = null): MarketplacePayout
    {
        $payout = MarketplacePayout::findOrFail($payoutId);
        
        if ($payout->status !== 'processing') {
            throw new \Exception('Only processing payouts can be marked as failed.');
        }
        
        $payout->update([
            'status' => 'failed',
            'rejection_reason' => $reason,
            'metadata' => $metadata,
        ]);
        
        // Refund amount to seller's available balance
        $payout->seller->increment('available_balance', $payout->amount);
        
        \Log::info('Payout failed', [
            'payout_id' => $payoutId,
            'reason' => $reason,
        ]);
        
        return $payout;
    }

    /**
     * Get payout statistics
     */
    public function getPayoutStats(): array
    {
        return [
            'pending_count' => MarketplacePayout::where('status', 'pending')->count(),
            'pending_amount' => MarketplacePayout::where('status', 'pending')->sum('net_amount'),
            'approved_count' => MarketplacePayout::where('status', 'approved')->count(),
            'approved_amount' => MarketplacePayout::where('status', 'approved')->sum('net_amount'),
            'processing_count' => MarketplacePayout::where('status', 'processing')->count(),
            'processing_amount' => MarketplacePayout::where('status', 'processing')->sum('net_amount'),
            'completed_today' => MarketplacePayout::where('status', 'completed')
                ->whereDate('processed_at', today())->count(),
            'completed_today_amount' => MarketplacePayout::where('status', 'completed')
                ->whereDate('processed_at', today())->sum('net_amount'),
            'completed_this_month' => MarketplacePayout::where('status', 'completed')
                ->whereMonth('processed_at', now()->month)->count(),
            'completed_this_month_amount' => MarketplacePayout::where('status', 'completed')
                ->whereMonth('processed_at', now()->month)->sum('net_amount'),
        ];
    }
}
