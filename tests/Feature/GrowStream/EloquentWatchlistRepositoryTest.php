<?php

use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\Video;
use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\Watchlist;
use App\Domain\GrowStream\Repositories\WatchlistRepositoryInterface;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

beforeEach(function () {
    $this->repo = app(WatchlistRepositoryInterface::class);
    $this->user = User::factory()->create();
    $this->video = Video::create([
        'uuid' => (string) Str::uuid(),
        'title' => 'Test Video',
        'slug' => Str::slug('Test Video '.uniqid()),
        'description' => 'A test video',
        'creator_id' => $this->user->id,
        'is_published' => true,
        'published_at' => now(),
        'upload_status' => 'ready',
        'access_level' => 'free',
    ]);
});

test('add to watchlist creates a record', function () {
    $watchlist = $this->repo->addToWatchlist($this->user->id, $this->video->id);

    expect($watchlist)->toBeInstanceOf(Watchlist::class);
    expect($watchlist->user_id)->toBe($this->user->id);
    expect($watchlist->watchlistable_id)->toBe($this->video->id);

    $this->assertDatabaseHas('growstream_watchlists', [
        'user_id' => $this->user->id,
        'watchlistable_id' => $this->video->id,
    ]);
});

test('add to watchlist is idempotent', function () {
    $this->repo->addToWatchlist($this->user->id, $this->video->id);
    $this->repo->addToWatchlist($this->user->id, $this->video->id);

    expect($this->repo->count($this->user->id))->toBe(1);
});

test('is in watchlist returns correct boolean', function () {
    expect($this->repo->isInWatchlist($this->user->id, $this->video->id))->toBeFalse();

    $this->repo->addToWatchlist($this->user->id, $this->video->id);

    expect($this->repo->isInWatchlist($this->user->id, $this->video->id))->toBeTrue();
});

test('remove from watchlist deletes the record', function () {
    $this->repo->addToWatchlist($this->user->id, $this->video->id);

    expect($this->repo->removeFromWatchlist($this->user->id, $this->video->id))->toBeTrue();
    expect($this->repo->isInWatchlist($this->user->id, $this->video->id))->toBeFalse();
});

test('remove from watchlist returns false when absent', function () {
    expect($this->repo->removeFromWatchlist($this->user->id, $this->video->id))->toBeFalse();
});

test('find by user returns only that users watchlist', function () {
    $otherUser = User::factory()->create();
    $otherVideo = Video::create([
        'uuid' => (string) Str::uuid(),
        'title' => 'Other Video',
        'slug' => Str::slug('Other Video '.uniqid()),
        'description' => 'Other',
        'creator_id' => $otherUser->id,
        'upload_status' => 'ready',
        'access_level' => 'free',
    ]);

    $this->repo->addToWatchlist($this->user->id, $this->video->id);
    $this->repo->addToWatchlist($otherUser->id, $otherVideo->id);

    $items = $this->repo->findByUser($this->user->id);
    expect($items)->toHaveCount(1);
    expect($items->first()->watchlistable_id)->toBe($this->video->id);
});

test('find by video returns all users who watchlisted the video', function () {
    $otherUser = User::factory()->create();

    $this->repo->addToWatchlist($this->user->id, $this->video->id);
    $this->repo->addToWatchlist($otherUser->id, $this->video->id);

    expect($this->repo->findByVideo($this->video->id))->toHaveCount(2);
});

test('get user watchlist paginates', function () {
    $this->repo->addToWatchlist($this->user->id, $this->video->id);

    $page = $this->repo->getUserWatchlist($this->user->id, 20);
    expect($page->total())->toBe(1);
    expect($page->first()->watchlistable)->not->toBeNull();
});

test('count supports per-user and global counts', function () {
    $otherUser = User::factory()->create();
    $otherVideo = Video::create([
        'uuid' => (string) Str::uuid(),
        'title' => 'Other Video',
        'slug' => Str::slug('Other Video '.uniqid()),
        'description' => 'Other',
        'creator_id' => $otherUser->id,
        'upload_status' => 'ready',
        'access_level' => 'free',
    ]);

    $this->repo->addToWatchlist($this->user->id, $this->video->id);
    $this->repo->addToWatchlist($otherUser->id, $otherVideo->id);

    expect($this->repo->count($this->user->id))->toBe(1);
    expect($this->repo->count())->toBe(2);
});

test('delete all clears a users watchlist', function () {
    $this->repo->addToWatchlist($this->user->id, $this->video->id);

    $this->repo->deleteAll($this->user->id);

    expect($this->repo->count($this->user->id))->toBe(0);
});

test('find by id returns the record', function () {
    $watchlist = $this->repo->addToWatchlist($this->user->id, $this->video->id);

    expect($this->repo->findById($watchlist->id)->id)->toBe($watchlist->id);
    expect($this->repo->findById(99999))->toBeNull();
});

test('query returns a builder', function () {
    expect($this->repo->query())->toBeInstanceOf(Builder::class);
});
