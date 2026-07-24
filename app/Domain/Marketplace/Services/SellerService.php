<?php

namespace App\Domain\Marketplace\Services;

use App\Domain\Marketplace\Entities\Seller;
use App\Domain\Marketplace\Repositories\SellerRepositoryInterface;
use App\Domain\Marketplace\ValueObjects\TrustLevel;
use App\Domain\Marketplace\ValueObjects\KycStatus;
use Illuminate\Support\Facades\DB;

class SellerService
{
    public function __construct(
        private SellerRepositoryInterface $sellerRepository,
    ) {}

    public function register(int $userId, array $data): array
    {
        $seller = new Seller(
            id: null,
            userId: $userId,
            businessName: $data['business_name'],
            businessType: $data['business_type'] ?? 'individual',
            province: $data['province'],
            district: $data['district'],
            trustLevel: TrustLevel::new(),
            kycStatus: KycStatus::pending(),
            kycDocuments: $data['kyc_documents'] ?? [],
            totalOrders: 0,
            completedOrders: 0,
            totalSalesAmount: 0,
            disputeRate: 0.0,
            cancellationRate: 0.0,
            rating: 0.0,
            isActive: false,
            commissionRate: null,
            phone: $data['phone'] ?? null,
            email: $data['email'] ?? null,
            description: $data['description'] ?? null,
            logoPath: null,
            coverImagePath: null,
            kycRejectionReason: null,
            tierCalculatedAt: null,
            createdAt: new \DateTimeImmutable(),
        );

        $saved = $this->sellerRepository->save($seller);
        return $saved->toArray();
    }

    public function getByUserId(int $userId): ?array
    {
        $seller = $this->sellerRepository->findByUserId($userId);
        return $seller ? $seller->toArray() : null;
    }

    public function getById(int $id): ?array
    {
        $seller = $this->sellerRepository->findById($id);
        return $seller ? $seller->toArray() : null;
    }

    public function getActiveSellers(array $filters = [], int $perPage = 20): array
    {
        $sellers = $this->sellerRepository->findActive($filters, $perPage);
        return array_map(fn(Seller $s) => $s->toArray(), $sellers);
    }

    public function approveKyc(int $sellerId): void
    {
        $this->sellerRepository->updateKycStatus($sellerId, 'approved');
        $seller = $this->sellerRepository->findById($sellerId);
        if ($seller) {
            $this->sellerRepository->updateTrustLevel($sellerId, 'verified');
            $this->sellerRepository->save(new Seller(
                id: $seller->id,
                userId: $seller->userId,
                businessName: $seller->businessName,
                businessType: $seller->businessType,
                province: $seller->province,
                district: $seller->district,
                trustLevel: TrustLevel::verified(),
                kycStatus: KycStatus::approved(),
                kycDocuments: $seller->kycDocuments,
                totalOrders: $seller->totalOrders,
                completedOrders: $seller->completedOrders,
                totalSalesAmount: $seller->totalSalesAmount,
                disputeRate: $seller->disputeRate,
                cancellationRate: $seller->cancellationRate,
                rating: $seller->rating,
                isActive: true,
                commissionRate: $seller->commissionRate,
                phone: $seller->phone,
                email: $seller->email,
                description: $seller->description,
                logoPath: $seller->logoPath,
                coverImagePath: $seller->coverImagePath,
                kycRejectionReason: $seller->kycRejectionReason,
                tierCalculatedAt: $seller->tierCalculatedAt,
                createdAt: $seller->createdAt,
            ));
        }
    }

    public function rejectKyc(int $sellerId, string $reason): void
    {
        $this->sellerRepository->updateKycStatus($sellerId, 'rejected');
    }

    public function updateProfile(int $sellerId, array $data): ?array
    {
        $seller = $this->sellerRepository->findById($sellerId);
        if (!$seller) return null;

        $updated = new Seller(
            id: $seller->id,
            userId: $seller->userId,
            businessName: $data['business_name'] ?? $seller->businessName,
            businessType: $data['business_type'] ?? $seller->businessType,
            province: $data['province'] ?? $seller->province,
            district: $data['district'] ?? $seller->district,
            trustLevel: $seller->trustLevel,
            kycStatus: $seller->kycStatus,
            kycDocuments: $seller->kycDocuments,
            totalOrders: $seller->totalOrders,
            completedOrders: $seller->completedOrders,
            totalSalesAmount: $seller->totalSalesAmount,
            disputeRate: $seller->disputeRate,
            cancellationRate: $seller->cancellationRate,
            rating: $seller->rating,
            isActive: $seller->isActive,
            commissionRate: $seller->commissionRate,
            phone: $data['phone'] ?? $seller->phone,
            email: $data['email'] ?? $seller->email,
            description: $data['description'] ?? $seller->description,
            logoPath: $data['logo_path'] ?? $seller->logoPath,
            coverImagePath: $data['cover_image_path'] ?? $seller->coverImagePath,
            kycRejectionReason: $seller->kycRejectionReason,
            tierCalculatedAt: $seller->tierCalculatedAt,
            createdAt: $seller->createdAt,
        );

        $saved = $this->sellerRepository->save($updated);
        return $saved->toArray();
    }

    public function updateTrustLevel(int $sellerId): void
    {
        $seller = $this->sellerRepository->findById($sellerId);
        if (!$seller) return;

        $newLevel = match (true) {
            $seller->totalOrders >= 200 && $seller->rating >= 4.8 => 'top',
            $seller->totalOrders >= 50 && $seller->rating >= 4.5 => 'trusted',
            $seller->kycStatus->isApproved() && $seller->totalOrders >= 5 => 'verified',
            default => $seller->trustLevel->value(),
        };

        if ($newLevel !== $seller->trustLevel->value()) {
            $this->sellerRepository->updateTrustLevel($sellerId, $newLevel);
        }
    }

    public function incrementOrderCount(int $sellerId): void
    {
        $this->sellerRepository->incrementOrderCount($sellerId);
        $this->updateTrustLevel($sellerId);
    }

    public function updateRating(int $sellerId): void
    {
        $avgRating = DB::table('marketplace_reviews')
            ->where('seller_id', $sellerId)
            ->where('is_approved', true)
            ->avg('rating');

        if ($avgRating) {
            $this->sellerRepository->updateRating($sellerId, round($avgRating, 2));
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
