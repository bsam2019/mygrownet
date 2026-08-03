<?php

use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\AttributionLink;
use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\CreatorProfile;
use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\VideoSeries;
use App\Domain\GrowStream\Services\AttributionService;
use App\Models\User;

function makeCreatorChannel(): CreatorProfile
{
    $user = User::factory()->create();

    return CreatorProfile::create([
        'user_id' => $user->id,
        'display_name' => 'Channel Creator',
        'channel_slug' => 'channel-creator-'.uniqid(),
        'status' => 'approved',
        'is_active' => true,
        'can_upload' => true,
    ]);
}

test('series defaults to one free episode', function () {
    $series = VideoSeries::create([
        'uuid' => (string) Str::uuid(),
        'title' => 'Test Series',
        'slug' => 'test-series-'.uniqid(),
        'description' => 'desc',
        'creator_id' => 1,
    ]);

    expect($series->isFreeEpisode(1))->toBeTrue()
        ->and($series->isFreeEpisode(2))->toBeFalse();
});

test('series honors free_episode_count override', function () {
    $series = VideoSeries::create([
        'uuid' => (string) Str::uuid(),
        'title' => 'Promo Series',
        'slug' => 'promo-series-'.uniqid(),
        'description' => 'desc',
        'creator_id' => 1,
        'free_episode_count' => 2,
    ]);

    expect($series->isFreeEpisode(1))->toBeTrue()
        ->and($series->isFreeEpisode(2))->toBeTrue()
        ->and($series->isFreeEpisode(3))->toBeFalse();
});

test('creator profile builds shareable channel url', function () {
    $creator = makeCreatorChannel();

    expect($creator->channelUrl('facebook'))->toBe('/c/'.$creator->channel_slug.'?src=facebook')
        ->and($creator->channelUrl())->toBe('/c/'.$creator->channel_slug);
});

test('attribution resolve deduplicates per session', function () {
    $creator = makeCreatorChannel();
    $service = app(AttributionService::class);

    $service->resolve($creator->id, 'facebook', 'sess-1');
    $service->resolve($creator->id, 'tiktok', 'sess-1');

    expect(AttributionLink::where('visitor_session_id', 'sess-1')->count())->toBe(1)
        ->and(AttributionLink::where('visitor_session_id', 'sess-1')->value('source'))->toBe('facebook');
});

test('attribution conversion binds user and watch minutes accumulate', function () {
    $creator = makeCreatorChannel();
    $service = app(AttributionService::class);

    $service->resolve($creator->id, 'whatsapp', 'sess-2');
    $service->recordConversion('sess-2', $creator->user_id);
    $service->accumulateWatchMinutes('sess-2', 10);

    $event = AttributionLink::where('visitor_session_id', 'sess-2')->first();

    expect($event->converted_user_id)->toBe($creator->user_id)
        ->and($event->watch_minutes_attributed)->toBe(10);
});
