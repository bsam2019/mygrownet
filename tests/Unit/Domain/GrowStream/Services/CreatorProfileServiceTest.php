<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\GrowStream\Services;

use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\CreatorProfile;
use App\Domain\GrowStream\Repositories\CreatorProfileRepositoryInterface;
use App\Domain\GrowStream\Repositories\VideoRepositoryInterface;
use App\Domain\GrowStream\Services\CreatorProfileService;
use App\Domain\GrowStream\ValueObjects\CreatorTier;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class CreatorProfileServiceTest extends TestCase
{
    private CreatorProfileRepositoryInterface&MockObject $repo;
    private VideoRepositoryInterface&MockObject $videoRepo;
    private CreatorProfileService $service;

    protected function setUp(): void
    {
        $this->repo = $this->createMock(CreatorProfileRepositoryInterface::class);
        $this->videoRepo = $this->createMock(VideoRepositoryInterface::class);
        $this->service = new CreatorProfileService($this->repo, $this->videoRepo);
    }

    private function mockProfile(array $overrides = []): CreatorProfile&MockObject
    {
        $attrs = array_merge([
            'id' => 1,
            'user_id' => 42,
            'display_name' => 'Test Creator',
            'bio' => 'A test bio',
            'avatar_url' => 'https://example.com/avatar.jpg',
            'banner_url' => 'https://example.com/banner.jpg',
            'creator_tier' => 'bronze',
            'total_views' => 100,
            'total_videos' => 5,
            'subscriber_count' => 20,
            'is_verified' => false,
            'is_active' => true,
            'can_upload' => true,
            'upload_limit_per_month' => 10,
            'created_at' => '2026-01-01T00:00:00Z',
            'updated_at' => '2026-01-01T00:00:00Z',
        ], $overrides);

        $profile = $this->getMockBuilder(CreatorProfile::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['__get', 'toArray', 'save', 'delete'])
            ->getMock();

        $profile->method('__get')->willReturnCallback(function ($key) use ($attrs) {
            if (isset($attrs[$key])) {
                $now = Carbon::now();
                if ($attrs[$key] === '__NOW__') return $now;
                return $attrs[$key];
            }
            return null;
        });

        $profile->method('toArray')->willReturn($attrs);

        return $profile;
    }

    #[Test]
    public function createProfile_createsSuccessfully(): void
    {
        $this->repo->expects($this->once())
            ->method('findByUserId')
            ->with(42)
            ->willReturn(null);

        $savedMock = $this->mockProfile(['id' => 1, 'user_id' => 42]);

        $this->repo->expects($this->once())
            ->method('save')
            ->with($this->callback(function ($data) {
                $this->assertSame(42, $data['user_id']);
                $this->assertSame('New Creator', $data['display_name']);
                $this->assertSame('bronze', $data['creator_tier']);
                $this->assertTrue($data['is_active']);
                $this->assertTrue($data['can_upload']);
                $this->assertSame(10, $data['upload_limit_per_month']);
                return true;
            }))
            ->willReturn($savedMock);

        $result = $this->service->createProfile(42, 'New Creator');

        $this->assertIsArray($result);
        $this->assertSame(1, $result['id']);
        $this->assertSame(42, $result['user_id']);
    }

    #[Test]
    public function createProfile_throwsWhenAlreadyExists(): void
    {
        $existing = $this->mockProfile();
        $this->repo->expects($this->once())
            ->method('findByUserId')
            ->with(42)
            ->willReturn($existing);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Creator profile already exists for user 42');

        $this->service->createProfile(42, 'New Creator');
    }

    #[Test]
    public function createProfile_withBioAndAvatar(): void
    {
        $this->repo->expects($this->once())
            ->method('findByUserId')
            ->with(10)
            ->willReturn(null);

        $savedMock = $this->mockProfile([
            'id' => 2,
            'user_id' => 10,
            'display_name' => 'Creator',
            'bio' => 'My custom bio',
            'avatar_url' => 'https://example.com/avatar.png',
            'creator_tier' => 'silver',
        ]);

        $this->repo->expects($this->once())
            ->method('save')
            ->with($this->callback(function ($data) {
                $this->assertSame('My custom bio', $data['bio']);
                $this->assertSame('https://example.com/avatar.png', $data['avatar_url']);
                $this->assertSame('silver', $data['creator_tier']);
                return true;
            }))
            ->willReturn($savedMock);

        $result = $this->service->createProfile(10, 'Creator', CreatorTier::Silver, 'My custom bio', 'https://example.com/avatar.png');

        $this->assertIsArray($result);
        $this->assertSame('My custom bio', $result['bio']);
    }

    #[Test]
    public function updateProfile_updatesFields(): void
    {
        $profile = $this->mockProfile();
        $this->repo->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($profile);

        $updatedMock = $this->mockProfile([
            'display_name' => 'Updated Name',
            'bio' => 'Updated bio',
            'avatar_url' => 'https://example.com/new-avatar.jpg',
            'banner_url' => 'https://example.com/new-banner.jpg',
        ]);

        $this->repo->expects($this->once())
            ->method('update')
            ->with($profile, $this->callback(function ($data) {
                $this->assertSame('Updated Name', $data['display_name']);
                $this->assertSame('Updated bio', $data['bio']);
                $this->assertSame('https://example.com/new-avatar.jpg', $data['avatar_url']);
                $this->assertSame('https://example.com/new-banner.jpg', $data['banner_url']);
                return true;
            }))
            ->willReturn($updatedMock);

        $result = $this->service->updateProfile(1, [
            'display_name' => 'Updated Name',
            'bio' => 'Updated bio',
            'avatar_url' => 'https://example.com/new-avatar.jpg',
            'banner_url' => 'https://example.com/new-banner.jpg',
        ]);

        $this->assertIsArray($result);
        $this->assertSame('Updated Name', $result['display_name']);
    }

    #[Test]
    public function updateProfile_updatesOnlyProvidedFields(): void
    {
        $profile = $this->mockProfile();
        $this->repo->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($profile);

        $updatedMock = $this->mockProfile(['display_name' => 'Only Name']);

        $this->repo->expects($this->once())
            ->method('update')
            ->with($profile, $this->callback(function ($data) {
                $this->assertCount(1, $data);
                $this->assertSame('Only Name', $data['display_name']);
                return true;
            }))
            ->willReturn($updatedMock);

        $result = $this->service->updateProfile(1, ['display_name' => 'Only Name']);
        $this->assertSame('Only Name', $result['display_name']);
    }

    #[Test]
    public function updateProfile_throwsWhenNotFound(): void
    {
        $this->repo->expects($this->once())
            ->method('findById')
            ->with(999)
            ->willReturn(null);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Creator profile not found: 999');

        $this->service->updateProfile(999, ['display_name' => 'Nope']);
    }

    #[Test]
    public function getProfile_returnsProfile(): void
    {
        $profile = $this->mockProfile();
        $this->repo->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($profile);

        $result = $this->service->getProfile(1);
        $this->assertIsArray($result);
        $this->assertSame(1, $result['id']);
        $this->assertSame(42, $result['user_id']);
    }

    #[Test]
    public function getProfile_returnsNullWhenNotFound(): void
    {
        $this->repo->expects($this->once())
            ->method('findById')
            ->with(999)
            ->willReturn(null);

        $result = $this->service->getProfile(999);
        $this->assertNull($result);
    }

    #[Test]
    public function getProfileByUserId_returnsProfile(): void
    {
        $profile = $this->mockProfile();
        $this->repo->expects($this->once())
            ->method('findByUserId')
            ->with(42)
            ->willReturn($profile);

        $result = $this->service->getProfileByUserId(42);
        $this->assertIsArray($result);
        $this->assertSame(42, $result['user_id']);
    }

    #[Test]
    public function getProfileByUserId_returnsNullNotFound(): void
    {
        $this->repo->expects($this->once())
            ->method('findByUserId')
            ->with(999)
            ->willReturn(null);

        $result = $this->service->getProfileByUserId(999);
        $this->assertNull($result);
    }

    #[Test]
    public function verifyProfile_verifiesSuccessfully(): void
    {
        $profile = $this->mockProfile(['is_verified' => false]);
        $this->repo->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($profile);

        $this->repo->expects($this->once())
            ->method('update')
            ->with($profile, $this->callback(function ($data) {
                $this->assertTrue($data['is_verified']);
                return true;
            }));

        $this->service->verifyProfile(1);
    }

    #[Test]
    public function verifyProfile_throwsWhenNotFound(): void
    {
        $this->repo->expects($this->once())
            ->method('findById')
            ->with(999)
            ->willReturn(null);

        $this->expectException(\RuntimeException::class);
        $this->service->verifyProfile(999);
    }

    #[Test]
    public function unverifyProfile_unverifiesSuccessfully(): void
    {
        $profile = $this->mockProfile(['is_verified' => true]);
        $this->repo->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($profile);

        $this->repo->expects($this->once())
            ->method('update')
            ->with($profile, $this->callback(function ($data) {
                $this->assertFalse($data['is_verified']);
                return true;
            }));

        $this->service->unverifyProfile(1);
    }

    #[Test]
    public function promoteTier_promotesSuccessfully(): void
    {
        $profile = $this->mockProfile(['creator_tier' => 'bronze']);
        $this->repo->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($profile);

        $this->repo->expects($this->once())
            ->method('update')
            ->with($profile, $this->callback(function ($data) {
                $this->assertSame('gold', $data['creator_tier']);
                return true;
            }));

        $this->service->promoteTier(1, CreatorTier::Gold);
    }

    #[Test]
    public function promoteTier_throwsWhenDowngrading(): void
    {
        $profile = $this->mockProfile(['creator_tier' => 'gold']);
        $this->repo->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($profile);

        $this->repo->expects($this->never())->method('update');

        $this->expectException(\RuntimeException::class);
        $this->service->promoteTier(1, CreatorTier::Bronze);
    }

    #[Test]
    public function promoteTier_throwsWhenNotFound(): void
    {
        $this->repo->expects($this->once())
            ->method('findById')
            ->with(999)
            ->willReturn(null);

        $this->expectException(\RuntimeException::class);
        $this->service->promoteTier(999, CreatorTier::Gold);
    }

    #[Test]
    public function suspendProfile_suspendsSuccessfully(): void
    {
        $profile = $this->mockProfile();
        $this->repo->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($profile);

        $this->repo->expects($this->once())
            ->method('update')
            ->with($profile, $this->callback(function ($data) {
                $this->assertFalse($data['is_active']);
                $this->assertFalse($data['can_upload']);
                return true;
            }));

        $this->service->suspendProfile(1, 'Violation of terms');
    }

    #[Test]
    public function suspendProfile_unpublishesVideos(): void
    {
        $profile = $this->mockProfile();
        $this->repo->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($profile);

        $this->repo->expects($this->once())
            ->method('update');

        $this->videoRepo->expects($this->once())
            ->method('findByCreator')
            ->with(1)
            ->willReturn(new Collection([
                (object) ['id' => 10],
                (object) ['id' => 11],
            ]));

        $this->videoRepo->expects($this->once())
            ->method('updateInBulk')
            ->with([10, 11], ['is_published' => false]);

        $this->service->suspendProfile(1, 'Violation', true);
    }

    #[Test]
    public function unsuspendProfile_unsuspendsSuccessfully(): void
    {
        $profile = $this->mockProfile(['is_active' => false, 'can_upload' => false]);
        $this->repo->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($profile);

        $this->repo->expects($this->once())
            ->method('update')
            ->with($profile, $this->callback(function ($data) {
                $this->assertTrue($data['is_active']);
                $this->assertTrue($data['can_upload']);
                return true;
            }));

        $this->service->unsuspendProfile(1);
    }

    #[Test]
    public function updateLimits_updatesLimits(): void
    {
        $profile = $this->mockProfile();
        $this->repo->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($profile);

        $this->repo->expects($this->once())
            ->method('update')
            ->with($profile, $this->callback(function ($data) {
                $this->assertFalse($data['can_upload']);
                $this->assertSame(50, $data['upload_limit_per_month']);
                return true;
            }));

        $this->service->updateLimits(1, [
            'can_upload' => false,
            'upload_limit_per_month' => 50,
        ]);
    }

    #[Test]
    public function recordStats_recordsViews(): void
    {
        $profile = $this->mockProfile(['total_views' => 100]);
        $this->repo->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($profile);

        $this->repo->expects($this->once())
            ->method('update')
            ->with($profile, $this->callback(function ($data) {
                $this->assertSame(105, $data['total_views']);
                return true;
            }));

        $this->service->recordStats(1, 'views', 5);
    }

    #[Test]
    public function recordStats_recordsSubscribers(): void
    {
        $profile = $this->mockProfile(['subscriber_count' => 20]);
        $this->repo->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($profile);

        $this->repo->expects($this->once())
            ->method('update')
            ->with($profile, $this->callback(function ($data) {
                $this->assertSame(22, $data['subscriber_count']);
                return true;
            }));

        $this->service->recordStats(1, 'subscribers', 2);
    }

    #[Test]
    public function recordStats_throwsForUnknownMetric(): void
    {
        $profile = $this->mockProfile();
        $this->repo->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($profile);

        $this->expectException(\InvalidArgumentException::class);
        $this->service->recordStats(1, 'likes', 5);
    }
}
