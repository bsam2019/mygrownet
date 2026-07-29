<?php

namespace Tests\Unit\Domain\GrowStream\Entities;

use App\Domain\GrowStream\Entities\CreatorProfile;
use App\Domain\GrowStream\ValueObjects\CreatorProfileId;
use App\Domain\GrowStream\ValueObjects\CreatorTier;
use PHPUnit\Framework\TestCase;

class CreatorProfileTest extends TestCase
{
    public function test_create_sets_defaults(): void
    {
        $profile = CreatorProfile::create(
            userId: 1,
            displayName: 'John Doe',
        );

        $this->assertNull($profile->id());
        $this->assertSame(1, $profile->userId());
        $this->assertSame('John Doe', $profile->displayName());
        $this->assertNull($profile->bio());
        $this->assertNull($profile->avatarUrl());
        $this->assertNull($profile->bannerUrl());
        $this->assertSame(CreatorTier::Bronze, $profile->tier());
        $this->assertSame(0, $profile->totalViews());
        $this->assertSame(0, $profile->totalVideos());
        $this->assertSame(0, $profile->totalSubscribers());
        $this->assertFalse($profile->isVerified());
        $this->assertSame([], $profile->socialLinks());
        $this->assertInstanceOf(\DateTimeImmutable::class, $profile->createdAt());
        $this->assertInstanceOf(\DateTimeImmutable::class, $profile->updatedAt());
    }

    public function test_create_with_custom_tier(): void
    {
        $profile = CreatorProfile::create(
            userId: 2,
            displayName: 'Jane',
            tier: CreatorTier::Gold,
        );

        $this->assertSame(CreatorTier::Gold, $profile->tier());
    }

    public function test_reconstitute_restores_profile(): void
    {
        $profile = CreatorProfile::reconstitute([
            'id' => 42,
            'user_id' => 5,
            'display_name' => 'Alice',
            'bio' => 'Video creator',
            'avatar_url' => 'https://example.com/avatar.jpg',
            'banner_url' => 'https://example.com/banner.jpg',
            'tier' => 'platinum',
            'total_views' => 1500,
            'total_videos' => 25,
            'total_subscribers' => 300,
            'is_verified' => 1,
            'social_links' => '{"youtube":"@alice","twitter":"@alice"}',
            'created_at' => '2026-01-01 00:00:00',
            'updated_at' => '2026-06-01 12:00:00',
        ]);

        $this->assertInstanceOf(CreatorProfileId::class, $profile->id());
        $this->assertEquals(42, $profile->id()->toInt());
        $this->assertSame(5, $profile->userId());
        $this->assertSame('Alice', $profile->displayName());
        $this->assertSame('Video creator', $profile->bio());
        $this->assertSame('https://example.com/avatar.jpg', $profile->avatarUrl());
        $this->assertSame('https://example.com/banner.jpg', $profile->bannerUrl());
        $this->assertSame(CreatorTier::Platinum, $profile->tier());
        $this->assertSame(1500, $profile->totalViews());
        $this->assertSame(25, $profile->totalVideos());
        $this->assertSame(300, $profile->totalSubscribers());
        $this->assertTrue($profile->isVerified());
        $this->assertSame(['youtube' => '@alice', 'twitter' => '@alice'], $profile->socialLinks());
    }

    public function test_reconstitute_with_minimal_data(): void
    {
        $profile = CreatorProfile::reconstitute([
            'user_id' => 10,
            'display_name' => 'Minimal',
            'tier' => 'bronze',
        ]);

        $this->assertNull($profile->id());
        $this->assertNull($profile->bio());
        $this->assertNull($profile->avatarUrl());
        $this->assertNull($profile->bannerUrl());
        $this->assertSame(0, $profile->totalViews());
        $this->assertSame(0, $profile->totalVideos());
        $this->assertSame(0, $profile->totalSubscribers());
        $this->assertFalse($profile->isVerified());
        $this->assertSame([], $profile->socialLinks());
    }

    public function test_verify_sets_verified(): void
    {
        $profile = CreatorProfile::create(userId: 1, displayName: 'Bob');
        $profile->verify();

        $this->assertTrue($profile->isVerified());
    }

    public function test_unverify_removes_verified(): void
    {
        $profile = CreatorProfile::create(userId: 1, displayName: 'Bob');
        $profile->verify();
        $profile->unverify();

        $this->assertFalse($profile->isVerified());
    }

    public function test_update_display_name(): void
    {
        $profile = CreatorProfile::create(userId: 1, displayName: 'Old Name');
        $profile->updateDisplayName('New Name');

        $this->assertSame('New Name', $profile->displayName());
    }

    public function test_update_bio(): void
    {
        $profile = CreatorProfile::create(userId: 1, displayName: 'Bob');
        $profile->updateBio('My new bio');

        $this->assertSame('My new bio', $profile->bio());
    }

    public function test_update_bio_to_null(): void
    {
        $profile = CreatorProfile::create(userId: 1, displayName: 'Bob');
        $profile->updateBio('Some bio');
        $profile->updateBio(null);

        $this->assertNull($profile->bio());
    }

    public function test_update_avatar(): void
    {
        $profile = CreatorProfile::create(userId: 1, displayName: 'Bob');
        $profile->updateAvatar('https://example.com/avatar.jpg');

        $this->assertSame('https://example.com/avatar.jpg', $profile->avatarUrl());
    }

    public function test_update_banner(): void
    {
        $profile = CreatorProfile::create(userId: 1, displayName: 'Bob');
        $profile->updateBanner('https://example.com/banner.jpg');

        $this->assertSame('https://example.com/banner.jpg', $profile->bannerUrl());
    }

    public function test_update_social_links(): void
    {
        $profile = CreatorProfile::create(userId: 1, displayName: 'Bob');
        $profile->updateSocialLinks(['youtube' => '@bob', 'twitter' => '@bob']);

        $this->assertSame(['youtube' => '@bob', 'twitter' => '@bob'], $profile->socialLinks());
    }

    public function test_increment_total_views_default(): void
    {
        $profile = CreatorProfile::create(userId: 1, displayName: 'Bob');
        $profile->incrementTotalViews();

        $this->assertSame(1, $profile->totalViews());
    }

    public function test_increment_total_views_custom_count(): void
    {
        $profile = CreatorProfile::create(userId: 1, displayName: 'Bob');
        $profile->incrementTotalViews(10);

        $this->assertSame(10, $profile->totalViews());
    }

    public function test_increment_total_videos(): void
    {
        $profile = CreatorProfile::create(userId: 1, displayName: 'Bob');
        $profile->incrementTotalVideos();
        $profile->incrementTotalVideos(3);

        $this->assertSame(4, $profile->totalVideos());
    }

    public function test_increment_total_subscribers(): void
    {
        $profile = CreatorProfile::create(userId: 1, displayName: 'Bob');
        $profile->incrementTotalSubscribers(5);

        $this->assertSame(5, $profile->totalSubscribers());
    }

    public function test_promote_to_higher_tier(): void
    {
        $profile = CreatorProfile::create(
            userId: 1,
            displayName: 'Bob',
            tier: CreatorTier::Bronze,
        );

        $profile->promoteTo(CreatorTier::Silver);
        $this->assertSame(CreatorTier::Silver, $profile->tier());

        $profile->promoteTo(CreatorTier::Platinum);
        $this->assertSame(CreatorTier::Platinum, $profile->tier());
    }

    public function test_promote_to_same_tier_throws(): void
    {
        $profile = CreatorProfile::create(
            userId: 1,
            displayName: 'Bob',
            tier: CreatorTier::Gold,
        );

        $this->expectException(\RuntimeException::class);
        $profile->promoteTo(CreatorTier::Gold);
    }

    public function test_promote_to_lower_tier_throws(): void
    {
        $profile = CreatorProfile::create(
            userId: 1,
            displayName: 'Bob',
            tier: CreatorTier::Platinum,
        );

        $this->expectException(\RuntimeException::class);
        $profile->promoteTo(CreatorTier::Bronze);
    }

    public function test_to_array_returns_all_fields(): void
    {
        $profile = CreatorProfile::create(
            userId: 7,
            displayName: 'Sam',
            tier: CreatorTier::Silver,
        );
        $profile->verify();
        $profile->updateBio('Bio text');
        $profile->updateAvatar('https://example.com/avatar.jpg');
        $profile->incrementTotalViews(50);
        $profile->incrementTotalVideos(5);
        $profile->incrementTotalSubscribers(20);

        $arr = $profile->toArray();

        $this->assertNull($arr['id']);
        $this->assertSame(7, $arr['user_id']);
        $this->assertSame('Sam', $arr['display_name']);
        $this->assertSame('Bio text', $arr['bio']);
        $this->assertSame('https://example.com/avatar.jpg', $arr['avatar_url']);
        $this->assertNull($arr['banner_url']);
        $this->assertSame('silver', $arr['tier']);
        $this->assertSame(50, $arr['total_views']);
        $this->assertSame(5, $arr['total_videos']);
        $this->assertSame(20, $arr['total_subscribers']);
        $this->assertTrue($arr['is_verified']);
        $this->assertSame([], $arr['social_links']);
    }
}
