<?php

namespace App\Domain\Marketplace\Services;

use App\Models\MarketplaceSeller;
use App\Models\MarketplaceOrder;
use App\Models\MarketplaceDispute;
use Illuminate\Support\Facades\DB;

class SellerTierService
{
    /**
     * Get commission rate for a seller based on their trust level
     */
    public function getCommissionRate(MarketplaceSeller $seller): float
    {
        // Use custom rate if set, otherwise use tier-based rate
        if ($seller->commission_rate && $seller->commission_rate > 0) {
            return $seller->commission_rate;
        }

        $rates = config('marketplace.commission.rates', []);
        return $rates[$seller->trust_level] ?? 10.0;
    }

    /**
     * Calculate commission amount for an order
     */
    public function calculateCommission(MarketplaceSeller $seller, int $orderAmount): array
    {
        $commissionRate = $this->getCommissionRate($seller);
        $processingFee = config('marketplace.commission.payment_processing_fee', 2.5);
        $minimumCommission = config('marketplace.commission.minimum_commission', 100);

        $commissionAmount = (int) round($orderAmount * ($commissionRate / 100));
        $processingAmount = (int) round($orderAmount * ($processingFee / 100));
        
        // Ensure minimum commission
        $commissionAmount = max($commissionAmount, $minimumCommission);
        
        $totalFees = $commissionAmount + $processingAmount;
        $sellerPayout = $orderAmount - $totalFees;

        return [
            'order_amount' => $orderAmount,
            'commission_rate' => $commissionRate,
            'commission_amount' => $commissionAmount,
            'processing_fee_rate' => $processingFee,
            'processing_fee_amount' => $processingAmount,
            'total_fees' => $totalFees,
            'seller_payout' => $sellerPayout,
        ];
    }

    /**
     * Update seller metrics and recalculate tier
     */
    public function updateSellerMetrics(MarketplaceSeller $seller): void
    {
        // Calculate metrics from orders
        $orderStats = MarketplaceOrder::where('seller_id', $seller->id)
            ->selectRaw('
                COUNT(*) as total_orders,
                SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed_orders,
                SUM(CASE WHEN status = "completed" THEN total ELSE 0 END) as total_sales,
                SUM(CASE WHEN status = "cancelled" THEN 1 ELSE 0 END) as cancelled_orders
            ')
            ->first();

        $disputeCount = MarketplaceDispute::where('seller_id', $seller->id)->count();

        $totalOrders = $orderStats->total_orders ?? 0;
        $completedOrders = $orderStats->completed_orders ?? 0;
        $totalSales = $orderStats->total_sales ?? 0;
        $cancelledOrders = $orderStats->cancelled_orders ?? 0;

        // Calculate rates
        $disputeRate = $completedOrders > 0 ? ($disputeCount / $completedOrders) * 100 : 0;
        $cancellationRate = $totalOrders > 0 ? ($cancelledOrders / $totalOrders) * 100 : 0;

        // Update seller metrics
        $seller->update([
            'total_orders' => $totalOrders,
            'completed_orders' => $completedOrders,
            'total_sales_amount' => $totalSales,
            'dispute_rate' => round($disputeRate, 2),
            'cancellation_rate' => round($cancellationRate, 2),
        ]);

        // Recalculate tier
        $this->recalculateTier($seller);
    }

    /**
     * Recalculate seller tier based on performance metrics
     */
    public function recalculateTier(MarketplaceSeller $seller): void
    {
        $seller->refresh();
        
        // Can't upgrade if not verified
        if ($seller->kyc_status !== 'approved') {
            return;
        }

        $currentTier = $seller->trust_level;
        $newTier = $this->determineEligibleTier($seller);

        if ($newTier !== $currentTier) {
            $newCommissionRate = config("marketplace.commission.rates.{$newTier}", 10.0);
            
            $seller->update([
                'trust_level' => $newTier,
                'commission_rate' => $newCommissionRate,
                'tier_calculated_at' => now(),
            ]);

            // TODO: Send notification about tier change
        }
    }

    /**
     * Determine the highest tier a seller is eligible for
     */
    private function determineEligibleTier(MarketplaceSeller $seller): string
    {
        $accountAgeDays = $seller->created_at->diffInDays(now());

        // Check for Top tier
        $topRequirements = config('marketplace.tiers.top');
        if ($this->meetsTierRequirements($seller, $topRequirements, $accountAgeDays)) {
            return 'top';
        }

        // Check for Trusted tier
        $trustedRequirements = config('marketplace.tiers.trusted');
        if ($this->meetsTierRequirements($seller, $trustedRequirements, $accountAgeDays)) {
            return 'trusted';
        }

        // Default to verified (if KYC approved) or new
        return $seller->kyc_status === 'approved' ? 'verified' : 'new';
    }

    /**
     * Check if seller meets all requirements for a tier
     */
    private function meetsTierRequirements(MarketplaceSeller $seller, array $requirements, int $accountAgeDays): bool
    {
        return $seller->completed_orders >= ($requirements['min_completed_orders'] ?? 0)
            && $seller->total_sales_amount >= ($requirements['min_total_sales'] ?? 0)
            && ($seller->rating ?? 0) >= ($requirements['min_rating'] ?? 0)
            && $seller->dispute_rate <= ($requirements['max_dispute_rate'] ?? 100)
            && $seller->cancellation_rate <= ($requirements['max_cancellation_rate'] ?? 100)
            && $accountAgeDays >= ($requirements['min_account_age_days'] ?? 0);
    }

    /**
     * Get tier progress for a seller (for dashboard display)
     */
    public function getTierProgress(MarketplaceSeller $seller): array
    {
        $currentTier = $seller->trust_level;
        $nextTier = $this->getNextTier($currentTier);
        
        if (!$nextTier) {
            return [
                'current_tier' => $currentTier,
                'next_tier' => null,
                'progress' => [],
                'is_max_tier' => true,
            ];
        }

        $requirements = config("marketplace.tiers.{$nextTier}", []);
        $accountAgeDays = $seller->created_at->diffInDays(now());

        $progress = [
            'completed_orders' => [
                'current' => $seller->completed_orders,
                'required' => $requirements['min_completed_orders'] ?? 0,
                'met' => $seller->completed_orders >= ($requirements['min_completed_orders'] ?? 0),
            ],
            'total_sales' => [
                'current' => $seller->total_sales_amount,
                'required' => $requirements['min_total_sales'] ?? 0,
                'met' => $seller->total_sales_amount >= ($requirements['min_total_sales'] ?? 0),
            ],
            'rating' => [
                'current' => $seller->rating ?? 0,
                'required' => $requirements['min_rating'] ?? 0,
                'met' => ($seller->rating ?? 0) >= ($requirements['min_rating'] ?? 0),
            ],
            'dispute_rate' => [
                'current' => $seller->dispute_rate,
                'required' => $requirements['max_dispute_rate'] ?? 100,
                'met' => $seller->dispute_rate <= ($requirements['max_dispute_rate'] ?? 100),
            ],
            'account_age' => [
                'current' => $accountAgeDays,
                'required' => $requirements['min_account_age_days'] ?? 0,
                'met' => $accountAgeDays >= ($requirements['min_account_age_days'] ?? 0),
            ],
        ];

        return [
            'current_tier' => $currentTier,
            'next_tier' => $nextTier,
            'progress' => $progress,
            'is_max_tier' => false,
        ];
    }

    /**
     * Get the next tier after the current one
     */
    private function getNextTier(string $currentTier): ?string
    {
        $tierOrder = ['new' => 'verified', 'verified' => 'trusted', 'trusted' => 'top', 'top' => null];
        return $tierOrder[$currentTier] ?? null;
    }

    /**
     * Get all tier information for display
     */
    public static function getTierInfo(): array
    {
        return [
            'new' => [
                'name' => 'New Seller',
                'badge' => '🆕',
                'commission' => config('marketplace.commission.rates.new', 10),
                'description' => 'Just getting started',
                'color' => 'gray',
            ],
            'verified' => [
                'name' => 'Verified Seller',
                'badge' => '✓',
                'commission' => config('marketplace.commission.rates.verified', 10),
                'description' => 'Identity verified, ready to sell',
                'color' => 'blue',
            ],
            'trusted' => [
                'name' => 'Trusted Seller',
                'badge' => '⭐',
                'commission' => config('marketplace.commission.rates.trusted', 8),
                'description' => 'Proven track record of quality service',
                'color' => 'amber',
            ],
            'top' => [
                'name' => 'Top Seller',
                'badge' => '👑',
                'commission' => config('marketplace.commission.rates.top', 5),
                'description' => 'Elite seller with exceptional performance',
                'color' => 'purple',
            ],
        ];
    }
}
