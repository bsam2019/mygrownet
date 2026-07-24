<?php

namespace App\Domain\Marketplace\Services;

use App\Domain\Marketplace\Repositories\SellerRepositoryInterface;
use Illuminate\Support\Facades\DB;

class SellerTierService
{
    public function __construct(
        private SellerRepositoryInterface $sellerRepository,
    ) {}

    public function getCommissionRate(array $seller): float
    {
        if (!empty($seller['commission_rate']) && $seller['commission_rate'] > 0) {
            return $seller['commission_rate'];
        }

        $rates = config('marketplace.commission.rates', []);
        return $rates[$seller['trust_level']] ?? 10.0;
    }

    public function calculateCommission(array $seller, int $orderAmount): array
    {
        $commissionRate = $this->getCommissionRate($seller);
        $processingFee = config('marketplace.commission.payment_processing_fee', 2.5);
        $minimumCommission = config('marketplace.commission.minimum_commission', 100);

        $commissionAmount = (int) round($orderAmount * ($commissionRate / 100));
        $processingAmount = (int) round($orderAmount * ($processingFee / 100));

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

    public function updateSellerMetrics(int $sellerId): void
    {
        $orderStats = DB::table('marketplace_orders')
            ->where('seller_id', $sellerId)
            ->selectRaw('
                COUNT(*) as total_orders,
                SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed_orders,
                SUM(CASE WHEN status = "completed" THEN total ELSE 0 END) as total_sales,
                SUM(CASE WHEN status = "cancelled" THEN 1 ELSE 0 END) as cancelled_orders
            ')
            ->first();

        $disputeCount = DB::table('marketplace_disputes')
            ->where('seller_id', $sellerId)
            ->count();

        $totalOrders = $orderStats->total_orders ?? 0;
        $completedOrders = $orderStats->completed_orders ?? 0;
        $totalSales = $orderStats->total_sales ?? 0;
        $cancelledOrders = $orderStats->cancelled_orders ?? 0;

        $disputeRate = $completedOrders > 0 ? ($disputeCount / $completedOrders) * 100 : 0;
        $cancellationRate = $totalOrders > 0 ? ($cancelledOrders / $totalOrders) * 100 : 0;

        $this->sellerRepository->updateSellerMetrics($sellerId, [
            'completed_orders' => $completedOrders,
            'total_sales_amount' => $totalSales,
            'dispute_rate' => round($disputeRate, 2),
            'cancellation_rate' => round($cancellationRate, 2),
        ]);
    }

    public function recalculateTier(int $sellerId): void
    {
        $this->updateSellerMetrics($sellerId);
        $seller = $this->sellerRepository->findById($sellerId);
        if (!$seller) return;

        if (!$seller->kycStatus->isApproved()) {
            return;
        }

        $newTier = $this->determineEligibleTier($seller);

        if ($newTier !== $seller->trustLevel->value()) {
            $newCommissionRate = config("marketplace.commission.rates.{$newTier}", 10.0);
            $this->sellerRepository->updateTierWithCommission($sellerId, $newTier, $newCommissionRate);
        }
    }

    private function determineEligibleTier(\App\Domain\Marketplace\Entities\Seller $seller): string
    {
        $accountAgeDays = $seller->createdAt ? $seller->createdAt->diff(new \DateTimeImmutable())->days : 0;

        $topRequirements = config('marketplace.tiers.top', []);
        if ($this->meetsTierRequirements($seller, $topRequirements, $accountAgeDays)) {
            return 'top';
        }

        $trustedRequirements = config('marketplace.tiers.trusted', []);
        if ($this->meetsTierRequirements($seller, $trustedRequirements, $accountAgeDays)) {
            return 'trusted';
        }

        return $seller->kycStatus->isApproved() ? 'verified' : 'new';
    }

    private function meetsTierRequirements(\App\Domain\Marketplace\Entities\Seller $seller, array $requirements, int $accountAgeDays): bool
    {
        return $seller->completedOrders >= ($requirements['min_completed_orders'] ?? 0)
            && $seller->totalSalesAmount >= ($requirements['min_total_sales'] ?? 0)
            && ($seller->rating ?? 0) >= ($requirements['min_rating'] ?? 0)
            && $seller->disputeRate <= ($requirements['max_dispute_rate'] ?? 100)
            && $seller->cancellationRate <= ($requirements['max_cancellation_rate'] ?? 100)
            && $accountAgeDays >= ($requirements['min_account_age_days'] ?? 0);
    }

    public function getTierProgress(array $seller): array
    {
        $currentTier = $seller['trust_level'];
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
        $createdAt = $seller['created_at'] ? new \DateTimeImmutable($seller['created_at']) : new \DateTimeImmutable();
        $accountAgeDays = $createdAt->diff(new \DateTimeImmutable())->days;

        $progress = [
            'completed_orders' => [
                'current' => $seller['completed_orders'] ?? 0,
                'required' => $requirements['min_completed_orders'] ?? 0,
                'met' => ($seller['completed_orders'] ?? 0) >= ($requirements['min_completed_orders'] ?? 0),
            ],
            'total_sales' => [
                'current' => $seller['total_sales_amount'] ?? 0,
                'required' => $requirements['min_total_sales'] ?? 0,
                'met' => ($seller['total_sales_amount'] ?? 0) >= ($requirements['min_total_sales'] ?? 0),
            ],
            'rating' => [
                'current' => $seller['rating'] ?? 0,
                'required' => $requirements['min_rating'] ?? 0,
                'met' => ($seller['rating'] ?? 0) >= ($requirements['min_rating'] ?? 0),
            ],
            'dispute_rate' => [
                'current' => $seller['dispute_rate'] ?? 0,
                'required' => $requirements['max_dispute_rate'] ?? 100,
                'met' => ($seller['dispute_rate'] ?? 0) <= ($requirements['max_dispute_rate'] ?? 100),
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

    private function getNextTier(string $currentTier): ?string
    {
        $tierOrder = ['new' => 'verified', 'verified' => 'trusted', 'trusted' => 'top', 'top' => null];
        return $tierOrder[$currentTier] ?? null;
    }

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
