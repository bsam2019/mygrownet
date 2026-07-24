<?php

declare(strict_types=1);

namespace App\Domain\BizBoost\Services;

use App\Domain\BizBoost\Entities\Business;
use App\Domain\BizBoost\Entities\BusinessProfile;
use App\Domain\BizBoost\Repositories\BusinessRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BusinessService
{
    public function __construct(
        private BusinessRepositoryInterface $businessRepo,
    ) {}

    public function getBusinessByUser(int $userId): ?Business
    {
        return $this->businessRepo->findByUserId($userId);
    }

    public function getBusinessOrFail(int $userId): Business
    {
        $business = $this->businessRepo->findByUserId($userId);
        if (!$business) {
            throw new \RuntimeException('Business not found');
        }
        return $business;
    }

    public function updateBusiness(int $id, array $data): ?Business
    {
        $existing = $this->businessRepo->findById($id);
        if (!$existing) {
            return null;
        }

        $merged = array_merge($existing->toArray(), $data);
        $merged['id'] = $id;
        return $this->businessRepo->save(Business::reconstitute($merged));
    }

    public function createOrUpdate(array $data): Business
    {
        $userId = (int) $data['user_id'];
        $existing = $this->businessRepo->findByUserId($userId);

        if ($existing) {
            return $this->updateBusiness($existing->id, $data);
        }

        $slug = $this->generateUniqueSlug($data['name']);
        $data['slug'] = $slug;

        $business = $this->businessRepo->save(Business::create($data));

        $this->businessRepo->saveProfile(new BusinessProfile(
            id: null,
            businessId: $business->id,
            heroImagePath: null,
            aboutImagePath: null,
            bannerImagePath: null,
            about: null,
            businessStory: null,
            mission: null,
            vision: null,
            foundingYear: null,
            businessHours: null,
            teamMembers: null,
            achievements: null,
            services: null,
            testimonials: null,
            tagline: null,
            contactEmail: $data['email'] ?? null,
            galleryImages: null,
            seoMeta: null,
            themeSettings: null,
            showProducts: true,
            showServices: true,
            showGallery: false,
            showTestimonials: false,
            showBusinessHours: true,
            showContactForm: true,
            showWhatsappButton: true,
            showSocialLinks: true,
            isPublished: false,
            createdAt: null,
            updatedAt: null,
        ));

        return $business;
    }

    public function createBusiness(int $userId, array $data): Business
    {
        $data['user_id'] = $userId;
        return $this->createOrUpdate($data);
    }

    public function completeOnboarding(int $businessId): void
    {
        $this->updateBusiness($businessId, ['onboarding_completed' => true]);
    }

    public function findBusinessBySlug(string $slug): ?Business
    {
        return $this->businessRepo->findBySlug($slug);
    }

    public function getProfile(int $businessId): ?BusinessProfile
    {
        return $this->businessRepo->findProfile($businessId);
    }

    public function saveProfile(BusinessProfile $profile): BusinessProfile
    {
        return $this->businessRepo->saveProfile($profile);
    }

    public function updateLogo(int $businessId, $logoFile): ?string
    {
        $business = $this->businessRepo->findById($businessId);
        if (!$business) {
            return null;
        }

        if ($business->logoPath) {
            Storage::disk('public')->delete($business->logoPath);
        }

        $path = $logoFile->store('bizboost/logos', 'public');
        $this->updateBusiness($businessId, ['logo_path' => $path]);

        return $path;
    }

    private function generateUniqueSlug(string $name): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $counter = 1;

        while ($this->businessRepo->findBySlug($slug)) {
            $slug = $baseSlug . '-' . $counter++;
        }

        return $slug;
    }
}