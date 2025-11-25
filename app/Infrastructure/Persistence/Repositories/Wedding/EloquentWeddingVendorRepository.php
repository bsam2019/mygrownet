<?php

namespace App\Infrastructure\Persistence\Repositories\Wedding;

use App\Domain\Wedding\Entities\WeddingVendor;
use App\Domain\Wedding\Repositories\WeddingVendorRepositoryInterface;
use App\Domain\Wedding\ValueObjects\VendorCategory;
use App\Domain\Wedding\ValueObjects\VendorRating;
use App\Infrastructure\Persistence\Eloquent\Wedding\WeddingVendorModel;

class EloquentWeddingVendorRepository implements WeddingVendorRepositoryInterface
{
    public function save(WeddingVendor $vendor): WeddingVendor
    {
        $model = $vendor->getId() 
            ? WeddingVendorModel::find($vendor->getId())
            : new WeddingVendorModel();

        $model->fill([
            'user_id' => $vendor->getUserId(),
            'business_name' => $vendor->getBusinessName(),
            'slug' => $vendor->getSlug(),
            'category' => $vendor->getCategory()->getValue(),
            'contact_person' => $vendor->getContactPerson(),
            'phone' => $vendor->getPhone(),
            'email' => $vendor->getEmail(),
            'location' => $vendor->getLocation(),
            'description' => $vendor->getDescription(),
            'price_range' => $vendor->getPriceRange(),
            'rating' => $vendor->getRating()->getRating(),
            'review_count' => $vendor->getRating()->getReviewCount(),
            'verified' => $vendor->isVerified(),
            'featured' => $vendor->isFeatured(),
            'services' => $vendor->getServices(),
            'portfolio_images' => $vendor->getPortfolioImages(),
            'availability' => $vendor->getAvailability()
        ]);

        $model->save();

        return $this->toDomainEntity($model);
    }

    public function findById(int $id): ?WeddingVendor
    {
        $model = WeddingVendorModel::find($id);
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findBySlug(string $slug): ?WeddingVendor
    {
        $model = WeddingVendorModel::where('slug', $slug)->first();
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findByUserId(int $userId): array
    {
        $models = WeddingVendorModel::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();

        return $models->map(fn($model) => $this->toDomainEntity($model))->toArray();
    }

    public function findByCategory(VendorCategory $category): array
    {
        $models = WeddingVendorModel::byCategory($category->getValue())
            ->verified()
            ->orderBy('rating', 'desc')
            ->get();

        return $models->map(fn($model) => $this->toDomainEntity($model))->toArray();
    }

    public function findByLocation(string $location): array
    {
        $models = WeddingVendorModel::byLocation($location)
            ->verified()
            ->orderBy('rating', 'desc')
            ->get();

        return $models->map(fn($model) => $this->toDomainEntity($model))->toArray();
    }

    public function findFeaturedVendors(int $limit = 10): array
    {
        $models = WeddingVendorModel::featured()
            ->verified()
            ->orderBy('rating', 'desc')
            ->limit($limit)
            ->get();

        return $models->map(fn($model) => $this->toDomainEntity($model))->toArray();
    }

    public function findVerifiedVendors(): array
    {
        $models = WeddingVendorModel::verified()
            ->orderBy('rating', 'desc')
            ->get();

        return $models->map(fn($model) => $this->toDomainEntity($model))->toArray();
    }

    public function searchVendors(array $criteria): array
    {
        $query = WeddingVendorModel::verified();

        if (isset($criteria['category'])) {
            $query->byCategory($criteria['category']);
        }

        if (isset($criteria['location'])) {
            $query->byLocation($criteria['location']);
        }

        if (isset($criteria['min_rating'])) {
            $query->where('rating', '>=', $criteria['min_rating']);
        }

        if (isset($criteria['search'])) {
            $search = $criteria['search'];
            $query->where(function($q) use ($search) {
                $q->where('business_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $models = $query->orderBy('rating', 'desc')->get();

        return $models->map(fn($model) => $this->toDomainEntity($model))->toArray();
    }

    public function findTopRatedVendors(int $limit = 10): array
    {
        $models = WeddingVendorModel::verified()
            ->highRated()
            ->orderBy('rating', 'desc')
            ->limit($limit)
            ->get();

        return $models->map(fn($model) => $this->toDomainEntity($model))->toArray();
    }

    public function delete(int $id): bool
    {
        return WeddingVendorModel::destroy($id) > 0;
    }

    public function getVendorStats(): array
    {
        return [
            'total_vendors' => WeddingVendorModel::count(),
            'verified_vendors' => WeddingVendorModel::verified()->count(),
            'featured_vendors' => WeddingVendorModel::featured()->count(),
            'high_rated_vendors' => WeddingVendorModel::highRated()->count(),
            'vendors_by_category' => WeddingVendorModel::verified()
                ->selectRaw('category, COUNT(*) as count')
                ->groupBy('category')
                ->pluck('count', 'category')
                ->toArray()
        ];
    }

    private function toDomainEntity(WeddingVendorModel $model): WeddingVendor
    {
        return new WeddingVendor(
            id: $model->id,
            userId: $model->user_id,
            businessName: $model->business_name,
            slug: $model->slug,
            category: VendorCategory::fromString($model->category),
            contactPerson: $model->contact_person,
            phone: $model->phone,
            email: $model->email,
            location: $model->location,
            description: $model->description,
            priceRange: $model->price_range,
            rating: VendorRating::fromRating($model->rating, $model->review_count),
            verified: $model->verified,
            featured: $model->featured,
            services: $model->services,
            portfolioImages: $model->portfolio_images,
            availability: $model->availability,
            createdAt: $model->created_at,
            updatedAt: $model->updated_at
        );
    }
}