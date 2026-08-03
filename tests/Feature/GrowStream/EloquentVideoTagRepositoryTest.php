<?php

use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\Video;
use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\VideoTag;
use App\Domain\GrowStream\Repositories\VideoTagRepositoryInterface;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

beforeEach(function () {
    $this->repo = app(VideoTagRepositoryInterface::class);
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

test('find or create reuses existing tag', function () {
    $first = $this->repo->findOrCreate('Comedy');
    $second = $this->repo->findOrCreate('Comedy');

    expect($first->id)->toBe($second->id);
    expect($first->slug)->toBe('comedy');
});

test('add tag attaches tag to video and increments usage', function () {
    $tag = $this->repo->addTag($this->video->id, 'Action');

    expect($tag)->toBeInstanceOf(VideoTag::class);
    expect($tag->usage_count)->toBe(1);

    $this->assertDatabaseHas('growstream_video_tag_pivot', [
        'video_id' => $this->video->id,
        'tag_id' => $tag->id,
    ]);
});

test('find by video returns attached tags', function () {
    $this->repo->addTag($this->video->id, 'Action');
    $this->repo->addTag($this->video->id, 'Drama');

    $tags = $this->repo->findByVideo($this->video->id);
    expect($tags)->toHaveCount(2);
    expect($tags->pluck('name')->sort()->values()->all())->toBe(['Action', 'Drama']);
});

test('find by tag returns matching tag records', function () {
    $this->repo->findOrCreate('Documentary');

    expect($this->repo->findByTag('Documentary'))->toHaveCount(1);
    expect($this->repo->findByTag('Missing'))->toHaveCount(0);
});

test('remove tag detaches tag and decrements usage', function () {
    $this->repo->addTag($this->video->id, 'Action');

    expect($this->repo->removeTag($this->video->id, 'Action'))->toBeTrue();
    expect($this->repo->findByVideo($this->video->id))->toHaveCount(0);
    expect($this->repo->findOrCreate('Action')->usage_count)->toBe(0);
});

test('remove tag returns false for absent tag', function () {
    expect($this->repo->removeTag($this->video->id, 'Action'))->toBeFalse();
});

test('get popular tags orders by usage count', function () {
    $videoB = Video::create([
        'uuid' => (string) Str::uuid(),
        'title' => 'Video B',
        'slug' => Str::slug('Video B '.uniqid()),
        'description' => 'B',
        'creator_id' => $this->user->id,
        'upload_status' => 'ready',
        'access_level' => 'free',
    ]);

    $this->repo->addTag($this->video->id, 'Popular');
    $this->repo->addTag($this->video->id, 'Popular');
    $this->repo->addTag($videoB->id, 'Popular');
    $this->repo->addTag($this->video->id, 'Rare');

    $popular = $this->repo->getPopularTags(10);

    expect($popular[0]['name'])->toBe('Popular');
    expect($popular[0]['usage_count'])->toBe(3);
    expect($popular[1]['name'])->toBe('Rare');
});

test('delete by video removes all pivot links and decrements usage', function () {
    $this->repo->addTag($this->video->id, 'Action');

    $this->repo->deleteByVideo($this->video->id);

    expect($this->repo->findByVideo($this->video->id))->toHaveCount(0);
    $this->assertDatabaseMissing('growstream_video_tag_pivot', ['video_id' => $this->video->id]);
    expect($this->repo->findOrCreate('Action')->usage_count)->toBe(0);
});

test('find by id returns the tag', function () {
    $tag = $this->repo->findOrCreate('Comedy');

    expect($this->repo->findById($tag->id)->id)->toBe($tag->id);
    expect($this->repo->findById(99999))->toBeNull();
});

test('query returns a builder', function () {
    expect($this->repo->query())->toBeInstanceOf(Builder::class);
});
