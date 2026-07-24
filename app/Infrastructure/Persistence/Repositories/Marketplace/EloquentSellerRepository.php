<?php

namespace App\Infrastructure\Persistence\Repositories\Marketplace;

use App\Domain\Marketplace\Entities\Seller;
use App\Domain\Marketplace\Repositories\SellerRepositoryInterface;
use App\Domain\Marketplace\ValueObjects\TrustLevel;
use App\Domain\Marketplace\ValueObjects\KycStatus;
use App\Infrastructure\Persistence\Eloquent\Marketplace\MarketplaceSeller;
use Illuminate\Support\Facades\DB;

class EloquentSellerRepository implements SellerRepositoryInterface
{
    public function findById(int $id): ?Seller
    {
        $model = MarketplaceSeller::find($id);
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findByUserId(int $userId): ?Seller
    {
        $model = MarketplaceSeller::where('user_id', $userId)->first();
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findActive(array $filters = [], int $perPage = 20): array
    {
        $query = MarketplaceSeller::where('is_active', true);

        if (!empty($filters['province'])) {
            $query->where('province', $filters['province']);
        }
        if (!empty($filters['trust_level'])) {
            $query->where('trust_level', $filters['trust_level']);
        }
        if (!empty($filters['business_type'])) {
            $query->where('business_type', $filters['business_type']);
        }
        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('business_name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('description', 'like', '%' . $filters['search'] . '%');
            });
        }

        return $query->orderBy('rating', 'desc')
            ->paginate($perPage)
            ->through(fn($m) => $this->toDomainEntity($m))
            ->toArray()['data'];
    }

    public function save(Seller $seller): Seller
    {
        $data = [
            'user_id' => $seller->userId,
            'business_name' => $seller->businessName,
            'business_type' => $seller->businessType,
            'province' => $seller->province,
            'district' => $seller->district,
            'trust_level' => $seller->trustLevel->value(),
            'kyc_status' => $seller->kycStatus->value(),
            'kyc_documents' => $seller->kycDocuments,
            'total_orders' => $seller->totalOrders,
            'completed_orders' => $seller->completedOrders,
            'total_sales_amount' => $seller->totalSalesAmount,
            'dispute_rate' => $seller->disputeRate,
            'cancellation_rate' => $seller->cancellationRate,
            'rating' => $seller->rating,
            'is_active' => $seller->isActive,
            'commission_rate' => $seller->commissionRate,
            'phone' => $seller->phone,
            'email' => $seller->email,
            'description' => $seller->description,
            'logo_path' => $seller->logoPath,
            'cover_image_path' => $seller->coverImagePath,
            'kyc_rejection_reason' => $seller->kycRejectionReason,
            'tier_calculated_at' => $seller->tierCalculatedAt?->format('Y-m-d H:i:s'),
        ];

        if ($seller->id) {
            MarketplaceSeller::where('id', $seller->id)->update($data);
            return $this->findById($seller->id);
        }

        $model = MarketplaceSeller::create($data);
        return $this->toDomainEntity($model);
    }

    public function updateTrustLevel(int $sellerId, string $trustLevel): void
    {
        MarketplaceSeller::where('id', $sellerId)->update(['trust_level' => $trustLevel]);
    }

    public function updateKycStatus(int $sellerId, string $status): void
    {
        MarketplaceSeller::where('id', $sellerId)->update(['kyc_status' => $status]);
    }

    public function incrementOrderCount(int $sellerId): void
    {
        MarketplaceSeller::where('id', $sellerId)->increment('total_orders');
    }

    public function updateRating(int $sellerId, float $rating): void
    {
        MarketplaceSeller::where('id', $sellerId)->update(['rating' => $rating]);
    }

    public function updateSellerMetrics(int $sellerId, array $metrics): void
    {
        $allowed = ['completed_orders', 'total_sales_amount', 'dispute_rate', 'cancellation_rate'];
        $data = array_intersect_key($metrics, array_flip($allowed));
        if (!empty($data)) {
            MarketplaceSeller::where('id', $sellerId)->update($data);
        }
    }

    public function updateTierWithCommission(int $sellerId, string $tier, float $commissionRate): void
    {
        MarketplaceSeller::where('id', $sellerId)->update([
            'trust_level' => $tier,
            'commission_rate' => $commissionRate,
            'tier_calculated_at' => now(),
        ]);
    }

    public function getBalance(int $sellerId): int
    {
        return (int) DB::table('marketplace_seller_balances')
            ->where('seller_id', $sellerId)
            ->value('available_balance') ?? 0;
    }

    public function decrementBalance(int $sellerId, int $amount): void
    {
        DB::table('marketplace_seller_balances')
            ->where('seller_id', $sellerId)
            ->decrement('available_balance', $amount);
    }

    public function incrementBalance(int $sellerId, int $amount): void
    {
        DB::table('marketplace_seller_balances')
            ->updateOrInsert(
                ['seller_id' => $sellerId],
                ['available_balance' => DB::raw("COALESCE(available_balance, 0) + {$amount}")]
            );
    }

    private function toDomainEntity(MarketplaceSeller $model): Seller
    {
        return new Seller(
            id: $model->id,
            userId: $model->user_id,
            businessName: $model->business_name,
            businessType: $model->business_type,
            province: $model->province,
            district: $model->district,
            trustLevel: TrustLevel::fromString($model->trust_level),
            kycStatus: KycStatus::fromString($model->kyc_status),
            kycDocuments: $model->kyc_documents ?? [],
            totalOrders: $model->total_orders ?? 0,
            completedOrders: $model->completed_orders ?? 0,
            totalSalesAmount: $model->total_sales_amount ?? 0,
            disputeRate: (float) ($model->dispute_rate ?? 0),
            cancellationRate: (float) ($model->cancellation_rate ?? 0),
            rating: (float) ($model->rating ?? 0.0),
            isActive: $model->is_active ?? true,
            commissionRate: $model->commission_rate ? (float) $model->commission_rate : null,
            phone: $model->phone,
            email: $model->email,
            description: $model->description,
            logoPath: $model->logo_path,
            coverImagePath: $model->cover_image_path,
            kycRejectionReason: $model->kyc_rejection_reason,
            tierCalculatedAt: $model->tier_calculated_at ? new \DateTimeImmutable($model->tier_calculated_at) : null,
            createdAt: $model->created_at ? new \DateTimeImmutable($model->created_at) : null,
        );
    }
}
