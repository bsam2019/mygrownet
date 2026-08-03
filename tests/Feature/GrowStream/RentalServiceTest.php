<?php

use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\CreatorProfile;
use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\Video;
use App\Domain\GrowStream\Services\RentalService;
use App\Domain\GrowStream\Services\WatchService;
use App\Models\User;
use Illuminate\Support\Str;

beforeEach(function () {
    $this->service = app(RentalService::class);
    $this->user = User::factory()->create();
    $creator = CreatorProfile::create([
        'user_id' => User::factory()->create()->id,
        'display_name' => 'Creator',
        'status' => 'approved',
        'is_active' => true,
    ]);
    $this->video = Video::create([
        'uuid' => (string) Str::uuid(),
        'title' => 'Rentable Movie',
        'slug' => Str::slug('rentable-'.uniqid()),
        'description' => 'desc',
        'creator_id' => $creator->id,
        'upload_status' => 'ready',
        'moderation_status' => 'approved',
        'content_type' => 'movie',
        'access_level' => 'premium',
        'is_published' => true,
        'published_at' => now(),
    ]);
});

test('rent creates an active rental with an expiry', function () {
    $rental = $this->service->rent($this->user->id, $this->video->id, 25.0, 'REF-1', '48_hours');

    expect($rental['user_id'])->toBe($this->user->id);
    expect($rental['video_id'])->toBe($this->video->id);
    expect((float) $rental['price'])->toBe(25.0);
    expect($rental['status'])->toBe('active');
    expect($rental['expires_at'])->not->toBeNull();

    expect($this->service->hasActiveRental($this->user->id, $this->video->id))->toBeTrue();
    $this->assertDatabaseHas('growstream_video_rentals', [
        'user_id' => $this->user->id,
        'video_id' => $this->video->id,
        'status' => 'active',
    ]);
});

test('renting the same video returns the existing active rental', function () {
    $this->service->rent($this->user->id, $this->video->id, 25.0, 'REF-1', '48_hours');
    $second = $this->service->rent($this->user->id, $this->video->id, 25.0, 'REF-2', '48_hours');

    expect($second['provider_reference'])->toBe('REF-1');
    $this->assertDatabaseCount('growstream_video_rentals', 1);
});

test('hasActiveRental is false when expired', function () {
    $this->service->rent($this->user->id, $this->video->id, 25.0, 'REF-1', '48_hours');

    $this->travelTo(now()->addDays(3));
    expect($this->service->hasActiveRental($this->user->id, $this->video->id))->toBeFalse();
});

test('rent supports different access durations', function () {
    $this->service->rent($this->user->id, $this->video->id, 40.0, 'REF-7', '7_days');

    $this->travelTo(now()->addDays(5));
    expect($this->service->hasActiveRental($this->user->id, $this->video->id))->toBeTrue();

    $this->travelTo(now()->addDays(10));
    expect($this->service->hasActiveRental($this->user->id, $this->video->id))->toBeFalse();
});

test('rent throws for invalid duration', function () {
    expect(fn () => $this->service->rent($this->user->id, $this->video->id, 25.0, null, 'invalid_duration'))
        ->toThrow(InvalidArgumentException::class);
});

test('rent throws for missing video', function () {
    expect(fn () => $this->service->rent($this->user->id, 99999, 25.0))
        ->toThrow(RuntimeException::class, 'Video not found');
});

test('watch service grants premium access via an active rental', function () {
    $this->service->rent($this->user->id, $this->video->id, 25.0, 'REF-1', '48_hours');

    $watchService = app(WatchService::class);
    expect($watchService->canWatch($this->video->id, $this->user->id))->toBeTrue();
});
