<?php

declare(strict_types=1);

namespace App\Domain\GrowStream\Services;

use App\Domain\GrowStream\Entities\CreatorProfile as CreatorProfileEntity;
use App\Domain\GrowStream\Exceptions\CreatorNotFoundException;
use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\CreatorProfile;
use App\Domain\GrowStream\Repositories\CreatorAgreementRepositoryInterface;
use App\Domain\GrowStream\Repositories\CreatorProfileRepositoryInterface;
use App\Domain\GrowStream\Repositories\VideoRepositoryInterface;
use App\Domain\GrowStream\ValueObjects\CreatorTier;
use Illuminate\Database\Eloquent\Collection;

class CreatorProfileService
{
    public function __construct(
        private CreatorProfileRepositoryInterface $repo,
        private ?VideoRepositoryInterface $videoRepo = null,
        private ?CreatorAgreementRepositoryInterface $agreementRepo = null,
    ) {}

    public function createProfile(int $userId, string $displayName, ?CreatorTier $tier = null, ?string $bio = null, ?string $avatarUrl = null): array
    {
        if ($this->repo->findByUserId($userId)) {
            throw CreatorNotFoundException::alreadyExistsForUser($userId);
        }

        $tier = $tier ?? CreatorTier::Bronze;
        $entity = CreatorProfileEntity::create($userId, $displayName, $tier);

        if ($bio !== null) {
            $entity->updateBio($bio);
        }
        if ($avatarUrl !== null) {
            $entity->updateAvatar($avatarUrl);
        }

        $data = [
            'user_id' => $entity->userId(),
            'display_name' => $entity->displayName(),
            'bio' => $entity->bio(),
            'avatar_url' => $entity->avatarUrl(),
            'banner_url' => $entity->bannerUrl(),
            'creator_tier' => $entity->tier()->value,
            'is_verified' => $entity->isVerified(),
            'total_views' => $entity->totalViews(),
            'total_videos' => $entity->totalVideos(),
            'subscriber_count' => $entity->totalSubscribers(),
            'is_active' => true,
            'can_upload' => true,
            'upload_limit_per_month' => 10,
        ];

        $saved = $this->repo->save($data);

        return $saved->toArray();
    }

    public function updateProfile(int $profileId, array $data): array
    {
        $profile = $this->repo->findById($profileId);
        if (! $profile) {
            throw CreatorNotFoundException::forId($profileId);
        }

        $updateData = [];
        if (isset($data['display_name'])) {
            $updateData['display_name'] = $data['display_name'];
        }
        if (array_key_exists('bio', $data)) {
            $updateData['bio'] = $data['bio'];
        }
        if (array_key_exists('avatar_url', $data)) {
            $updateData['avatar_url'] = $data['avatar_url'];
        }
        if (array_key_exists('banner_url', $data)) {
            $updateData['banner_url'] = $data['banner_url'];
        }

        $updated = $this->repo->update($profile, $updateData);

        return $updated->toArray();
    }

    public function applyForCreator(int $userId, array $application): array
    {
        if ($this->repo->findByUserId($userId)) {
            throw CreatorNotFoundException::alreadyExistsForUser($userId);
        }

        $data = [
            'user_id' => $userId,
            'display_name' => $application['display_name'],
            'channel_name' => $application['channel_name'] ?? null,
            'bio' => $application['bio'] ?? null,
            'website_url' => $application['website_url'] ?? null,
            'facebook_url' => $application['facebook_url'] ?? null,
            'twitter_url' => $application['twitter_url'] ?? null,
            'instagram_url' => $application['instagram_url'] ?? null,
            'youtube_url' => $application['youtube_url'] ?? null,
            'status' => 'pending',
            'is_active' => false,
            'is_verified' => false,
            'creator_tier' => CreatorTier::Bronze->value,
            'can_upload' => false,
            'upload_limit_per_month' => (int) config('growstream.creator.upload_limit_per_month', 50),
            'revenue_share_percentage' => (float) config('growstream.creator.default_revenue_share', 70),
        ];

        $saved = $this->repo->save($data);

        return $saved->toArray();
    }

    public function approveCreator(int $profileId): array
    {
        $profile = $this->repo->findById($profileId);
        if (! $profile) {
            throw CreatorNotFoundException::forId($profileId);
        }

        return $this->repo->update($profile, [
            'status' => 'approved',
            'is_active' => true,
            'can_upload' => true,
            'rejected_reason' => null,
        ])->toArray();
    }

    public function rejectCreator(int $profileId, string $reason): array
    {
        $profile = $this->repo->findById($profileId);
        if (! $profile) {
            throw CreatorNotFoundException::forId($profileId);
        }

        return $this->repo->update($profile, [
            'status' => 'rejected',
            'is_active' => false,
            'can_upload' => false,
            'rejected_reason' => $reason,
        ])->toArray();
    }

    public function acceptAgreement(int $profileId, string $version, ?string $ipAddress = null, ?string $userAgent = null): void
    {
        $profile = $this->repo->findById($profileId);
        if (! $profile) {
            throw CreatorNotFoundException::forId($profileId);
        }

        if ($this->agreementRepo) {
            $this->agreementRepo->recordAcceptance($profileId, $version, $ipAddress, $userAgent);
        }
    }

    public function hasAcceptedAgreement(int $profileId, string $version): bool
    {
        return $this->agreementRepo && $this->agreementRepo->hasAccepted($profileId, $version);
    }

    public function pendingCreators(array $relations = []): Collection
    {
        return $this->repo->query()
            ->where('status', 'pending')
            ->with($relations)
            ->latest()
            ->get();
    }

    public function pendingCreatorCount(): int
    {
        return $this->repo->query()->where('status', 'pending')->count();
    }

    private function findOrFail(int $profileId): CreatorProfile
    {
        $profile = $this->repo->findById($profileId);
        if (! $profile) {
            throw CreatorNotFoundException::forId($profileId);
        }

        return $profile;
    }

    public function getProfile(int $profileId): ?array
    {
        $profile = $this->repo->findById($profileId);

        return $profile?->toArray();
    }

    public function getProfileByUserId(int $userId): ?array
    {
        $profile = $this->repo->findByUserId($userId);

        return $profile?->toArray();
    }

    public function verifyProfile(int $profileId): void
    {
        $profile = $this->repo->findById($profileId);
        if (! $profile) {
            throw CreatorNotFoundException::forId($profileId);
        }

        $entity = CreatorProfileEntity::reconstitute([
            'id' => $profile->id,
            'user_id' => $profile->user_id,
            'display_name' => $profile->display_name,
            'bio' => $profile->bio,
            'avatar_url' => $profile->avatar_url,
            'banner_url' => $profile->banner_url,
            'tier' => $profile->creator_tier,
            'total_views' => $profile->total_views,
            'total_videos' => $profile->total_videos,
            'total_subscribers' => $profile->subscriber_count,
            'is_verified' => $profile->is_verified,
            'social_links' => [],
        ]);

        $entity->verify();

        $this->repo->update($profile, ['is_verified' => true]);
    }

    public function unverifyProfile(int $profileId): void
    {
        $profile = $this->repo->findById($profileId);
        if (! $profile) {
            throw CreatorNotFoundException::forId($profileId);
        }

        $entity = CreatorProfileEntity::reconstitute([
            'id' => $profile->id,
            'user_id' => $profile->user_id,
            'display_name' => $profile->display_name,
            'bio' => $profile->bio,
            'avatar_url' => $profile->avatar_url,
            'banner_url' => $profile->banner_url,
            'tier' => $profile->creator_tier,
            'total_views' => $profile->total_views,
            'total_videos' => $profile->total_videos,
            'total_subscribers' => $profile->subscriber_count,
            'is_verified' => $profile->is_verified,
            'social_links' => [],
        ]);

        $entity->unverify();

        $this->repo->update($profile, ['is_verified' => false]);
    }

    public function promoteTier(int $profileId, CreatorTier $newTier): void
    {
        $profile = $this->repo->findById($profileId);
        if (! $profile) {
            throw CreatorNotFoundException::forId($profileId);
        }

        $entity = CreatorProfileEntity::reconstitute([
            'id' => $profile->id,
            'user_id' => $profile->user_id,
            'display_name' => $profile->display_name,
            'bio' => $profile->bio,
            'avatar_url' => $profile->avatar_url,
            'banner_url' => $profile->banner_url,
            'tier' => $profile->creator_tier,
            'total_views' => $profile->total_views,
            'total_videos' => $profile->total_videos,
            'total_subscribers' => $profile->subscriber_count,
            'is_verified' => $profile->is_verified,
            'social_links' => [],
        ]);

        $entity->promoteTo($newTier);

        $this->repo->update($profile, ['creator_tier' => $newTier->value]);
    }

    public function suspendProfile(int $profileId, string $reason, bool $unpublishVideos = false): void
    {
        $profile = $this->repo->findById($profileId);
        if (! $profile) {
            throw CreatorNotFoundException::forId($profileId);
        }

        $this->repo->update($profile, [
            'is_active' => false,
            'can_upload' => false,
        ]);

        if ($unpublishVideos && $this->videoRepo) {
            $videos = $this->videoRepo->findByCreator($profileId);
            $videoIds = $videos->pluck('id')->toArray();
            if (! empty($videoIds)) {
                $this->videoRepo->updateInBulk($videoIds, ['is_published' => false]);
            }
        }
    }

    public function unsuspendProfile(int $profileId): void
    {
        $profile = $this->repo->findById($profileId);
        if (! $profile) {
            throw CreatorNotFoundException::forId($profileId);
        }

        $this->repo->update($profile, [
            'is_active' => true,
            'can_upload' => true,
        ]);
    }

    public function updateLimits(int $profileId, array $limits): void
    {
        $profile = $this->repo->findById($profileId);
        if (! $profile) {
            throw CreatorNotFoundException::forId($profileId);
        }

        $updateData = [];
        if (isset($limits['can_upload'])) {
            $updateData['can_upload'] = (bool) $limits['can_upload'];
        }
        if (isset($limits['upload_limit_per_month'])) {
            $updateData['upload_limit_per_month'] = (int) $limits['upload_limit_per_month'];
        }

        $this->repo->update($profile, $updateData);
    }

    public function recordStats(int $profileId, string $metric, int $count = 1): void
    {
        $profile = $this->repo->findById($profileId);
        if (! $profile) {
            throw CreatorNotFoundException::forId($profileId);
        }

        $entity = CreatorProfileEntity::reconstitute([
            'id' => $profile->id,
            'user_id' => $profile->user_id,
            'display_name' => $profile->display_name,
            'bio' => $profile->bio,
            'avatar_url' => $profile->avatar_url,
            'banner_url' => $profile->banner_url,
            'tier' => $profile->creator_tier,
            'total_views' => $profile->total_views,
            'total_videos' => $profile->total_videos,
            'total_subscribers' => $profile->subscriber_count,
            'is_verified' => $profile->is_verified,
            'social_links' => [],
        ]);

        match ($metric) {
            'views' => $entity->incrementTotalViews($count),
            'videos' => $entity->incrementTotalVideos($count),
            'subscribers' => $entity->incrementTotalSubscribers($count),
            default => throw new \InvalidArgumentException("Unknown metric: {$metric}"),
        };

        $this->repo->update($profile, [
            'total_views' => $entity->totalViews(),
            'total_videos' => $entity->totalVideos(),
            'subscriber_count' => $entity->totalSubscribers(),
        ]);
    }
}
