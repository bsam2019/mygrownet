<?php

use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\CreatorEarning;
use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\CreatorProfile;
use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\Video;
use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\WatchHistory;
use App\Domain\GrowStream\Services\RevenuePoolService;
use App\Models\User;
use Illuminate\Support\Str;

beforeEach(function () {
    $this->service = app(RevenuePoolService::class);
    $this->creator = CreatorProfile::create([
        'user_id' => User::factory()->create()->id,
        'display_name' => 'Creator A',
        'status' => 'approved',
        'is_active' => true,
    ]);
});

function makePremiumVideo(CreatorProfile $creator): Video
{
    return Video::create([
        'uuid' => (string) Str::uuid(),
        'title' => 'Premium Video '.uniqid(),
        'slug' => Str::slug('premium-video-'.uniqid()),
        'description' => 'desc',
        'creator_id' => $creator->id,
        'upload_status' => 'ready',
        'moderation_status' => 'approved',
        'content_type' => 'movie',
        'access_level' => 'premium',
        'is_published' => true,
        'published_at' => now(),
    ]);
}

function recordWatch(Video $video, User $user, int $seconds): void
{
    WatchHistory::create([
        'user_id' => $user->id,
        'video_id' => $video->id,
        'current_position' => $seconds,
        'watch_duration' => $seconds,
        'duration' => 3600,
        'progress_percentage' => ($seconds / 3600) * 100,
        'started_at' => now(),
        'last_watched_at' => now(),
    ]);
}

test('calculates earnings proportionally to premium watch seconds', function () {
    $otherCreator = CreatorProfile::create([
        'user_id' => User::factory()->create()->id,
        'display_name' => 'Creator B',
        'status' => 'approved',
        'is_active' => true,
    ]);

    $viewer = User::factory()->create();

    // 200 seconds for creator A, 100 seconds for creator B
    recordWatch(makePremiumVideo($this->creator), $viewer, 200);
    recordWatch(makePremiumVideo($this->creator), $viewer, 100);
    recordWatch(makePremiumVideo($otherCreator), $viewer, 100);

    $start = now()->startOfMonth();
    $end = now()->endOfMonth();

    $earnings = $this->service->calculateForPeriod($start, $end, 1000.0);

    // pool = 1000 * 0.70 = 700
    // A: 300/400 * 700 = 525, B: 100/400 * 700 = 175
    $byCreator = collect($earnings)->keyBy('creator_id');

    expect((float) $byCreator->get($this->creator->id)['earned_amount'])->toBe(525.0);
    expect((float) $byCreator->get($otherCreator->id)['earned_amount'])->toBe(175.0);
    expect((int) $byCreator->get($this->creator->id)['premium_watch_seconds'])->toBe(300);
});

test('free videos are excluded from the premium pool', function () {
    $freeVideo = Video::create([
        'uuid' => (string) Str::uuid(),
        'title' => 'Free Video',
        'slug' => Str::slug('free-video-'.uniqid()),
        'description' => 'desc',
        'creator_id' => $this->creator->id,
        'upload_status' => 'ready',
        'moderation_status' => 'approved',
        'content_type' => 'movie',
        'access_level' => 'free',
        'is_published' => true,
        'published_at' => now(),
    ]);

    $viewer = User::factory()->create();
    recordWatch($freeVideo, $viewer, 500);

    $earnings = $this->service->calculateForPeriod(now()->startOfMonth(), now()->endOfMonth(), 1000.0);

    expect($earnings)->toBeEmpty();
});

test('returns empty when no premium watch activity', function () {
    $earnings = $this->service->calculateForPeriod(now()->startOfMonth(), now()->endOfMonth(), 1000.0);

    expect($earnings)->toBeEmpty();
});

test('re-running the same period upserts rather than duplicating', function () {
    $viewer = User::factory()->create();
    recordWatch(makePremiumVideo($this->creator), $viewer, 100);

    $start = now()->startOfMonth();
    $end = now()->endOfMonth();

    $this->service->calculateForPeriod($start, $end, 1000.0);
    $this->service->calculateForPeriod($start, $end, 1000.0);

    $count = CreatorEarning::where('creator_id', $this->creator->id)->count();
    expect($count)->toBe(1);
});
