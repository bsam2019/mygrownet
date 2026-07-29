<?php

declare(strict_types=1);

namespace App\Domain\GrowStream\Services;

use App\Domain\GrowStream\Entities\CreatorProfile as CreatorProfileEntity;
use App\Domain\GrowStream\Repositories\CreatorProfileRepositoryInterface;
use App\Domain\GrowStream\Repositories\VideoRepositoryInterface;
use App\Domain\GrowStream\ValueObjects\CreatorTier;

class CreatorProfileService
{
    public function __construct(
        private CreatorProfileRepositoryInterface $repo,
        private ?VideoRepositoryInterface $videoRepo = null,
    ) {}

    public function createProfile(int $userId, string $displayName, CreatorTier $tier = null, ?string $bio = null, ?string $avatarUrl = null): array
    {
        if ($this->repo->findByUserId($userId)) {
            throw new \RuntimeException("Creator profile already exists for user {$userId}");
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
        if (!$profile) {
            throw new \RuntimeException("Creator profile not found: {$profileId}");
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
        if (!$profile) {
            throw new \RuntimeException("Creator profile not found: {$profileId}");
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
        if (!$profile) {
            throw new \RuntimeException("Creator profile not found: {$profileId}");
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
        if (!$profile) {
            throw new \RuntimeException("Creator profile not found: {$profileId}");
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
        if (!$profile) {
            throw new \RuntimeException("Creator profile not found: {$profileId}");
        }

        $this->repo->update($profile, [
            'is_active' => false,
            'can_upload' => false,
        ]);

        if ($unpublishVideos && $this->videoRepo) {
            $videos = $this->videoRepo->findByCreator($profileId);
            $videoIds = $videos->pluck('id')->toArray();
            if (!empty($videoIds)) {
                $this->videoRepo->updateInBulk($videoIds, ['is_published' => false]);
            }
        }
    }

    public function unsuspendProfile(int $profileId): void
    {
        $profile = $this->repo->findById($profileId);
        if (!$profile) {
            throw new \RuntimeException("Creator profile not found: {$profileId}");
        }

        $this->repo->update($profile, [
            'is_active' => true,
            'can_upload' => true,
        ]);
    }

    public function updateLimits(int $profileId, array $limits): void
    {
        $profile = $this->repo->findById($profileId);
        if (!$profile) {
            throw new \RuntimeException("Creator profile not found: {$profileId}");
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
        if (!$profile) {
            throw new \RuntimeException("Creator profile not found: {$profileId}");
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
