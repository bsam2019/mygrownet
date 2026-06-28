<?php

namespace App\Domain\Marketplace\Services;

use App\Domain\Marketplace\Entities\Seller;
use App\Domain\Marketplace\ValueObjects\TrustLevel;
use App\Domain\Marketplace\ValueObjects\KycStatus;
use App\Models\MarketplaceSeller;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class SellerService
{
    public function register(int $userId, array $data): MarketplaceSeller
    {
        return MarketplaceSeller::create([
            'user_id' => $userId,
            'business_name' => $data['business_name'],
            'business_type' => $data['business_type'] ?? 'individual',
            'province' => $data['province'],
            'district' => $data['district'],
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'description' => $data['description'] ?? null,
            'trust_level' => 'new',
            'kyc_status' => 'pending',
            'kyc_documents' => $data['kyc_documents'] ?? [],
            'is_active' => false,
        ]);
    }

    public function getByUserId(int $userId): ?MarketplaceSeller
    {
        return MarketplaceSeller::where('user_id', $userId)->first();
    }

    public function getById(int $id): ?MarketplaceSeller
    {
        return MarketplaceSeller::with('user')->find($id);
    }

    public function getActiveSellers(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = MarketplaceSeller::with('user')
            ->where('is_active', true)
            ->where('kyc_status', 'approved');

        if (!empty($filters['province'])) {
            $query->where('province', $filters['province']);
        }

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('business_name', 'like', "%{$filters['search']}%")
                  ->orWhere('description', 'like', "%{$filters['search']}%");
            });
        }

        if (!empty($filters['trust_level'])) {
            $query->where('trust_level', $filters['trust_level']);
        }

        return $query->orderByDesc('rating')
            ->orderByDesc('total_orders')
            ->paginate($perPage);
    }

    public function approveKyc(int $sellerId): void
    {
        $seller = MarketplaceSeller::findOrFail($sellerId);
        $seller->update([
            'kyc_status' => 'approved',
            'is_active' => true,
            'trust_level' => 'verified',
        ]);
    }

    public function rejectKyc(int $sellerId, string $reason): void
    {
        $seller = MarketplaceSeller::findOrFail($sellerId);
        $seller->update([
            'kyc_status' => 'rejected',
            'kyc_rejection_reason' => $reason,
        ]);
    }

    public function updateProfile(int $sellerId, array $data): MarketplaceSeller
    {
        $seller = MarketplaceSeller::findOrFail($sellerId);
        $seller->update($data);
        return $seller->fresh();
    }

    public function updateTrustLevel(int $sellerId): void
    {
        $seller = MarketplaceSeller::findOrFail($sellerId);
        
        $newLevel = match (true) {
            $seller->total_orders >= 200 && $seller->rating >= 4.8 => 'top',
            $seller->total_orders >= 50 && $seller->rating >= 4.5 => 'trusted',
            $seller->kyc_status === 'approved' && $seller->total_orders >= 5 => 'verified',
            default => $seller->trust_level,
        };

        if ($newLevel !== $seller->trust_level) {
            $seller->update(['trust_level' => $newLevel]);
        }
    }

    public function incrementOrderCount(int $sellerId): void
    {
        MarketplaceSeller::where('id', $sellerId)->increment('total_orders');
        $this->updateTrustLevel($sellerId);
    }

    public function updateRating(int $sellerId): void
    {
        $avgRating = DB::table('marketplace_reviews')
            ->where('seller_id', $sellerId)
            ->avg('rating');

        if ($avgRating) {
            MarketplaceSeller::where('id', $sellerId)
                ->update(['rating' => round($avgRating, 2)]);
            $this->updateTrustLevel($sellerId);
        }
    }

    public function getProvinces(): array
    {
        return [
            'Central', 'Copperbelt', 'Eastern', 'Luapula',
            'Lusaka', 'Muchinga', 'Northern', 'North-Western',
            'Southern', 'Western',
        ];
    }
}
