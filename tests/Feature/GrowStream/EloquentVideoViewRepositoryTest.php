<?php

use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\Video;
use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\VideoView;
use App\Domain\GrowStream\Repositories\VideoViewRepositoryInterface;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

beforeEach(function () {
    $this->repo = app(VideoViewRepositoryInterface::class);
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

test('record view persists a view', function () {
    $view = $this->repo->recordView($this->video->id, $this->user->id, '127.0.0.1', 'test-agent');

    expect($view)->toBeInstanceOf(VideoView::class);
    expect($view->video_id)->toBe($this->video->id);
    expect($view->user_id)->toBe($this->user->id);
    expect($view->viewed_at)->not->toBeNull();

    $this->assertDatabaseHas('growstream_video_views', ['video_id' => $this->video->id]);
});

test('get views by video filters by date range', function () {
    $this->repo->recordView($this->video->id, $this->user->id);
    $this->repo->recordView($this->video->id, $this->user->id);

    $from = now()->subDay();
    $to = now()->addDay();
    expect($this->repo->getViewsByVideo($this->video->id, $from, $to))->toHaveCount(2);

    $futureFrom = now()->addDays(2);
    expect($this->repo->getViewsByVideo($this->video->id, $futureFrom))->toHaveCount(0);
});

test('get views by date counts views for a single day', function () {
    $this->repo->recordView($this->video->id, $this->user->id);
    $this->repo->recordView($this->video->id, $this->user->id);

    expect($this->repo->getViewsByDate($this->video->id, now()))->toBe(2);
});

test('get total views counts all views', function () {
    $this->repo->recordView($this->video->id, $this->user->id);
    $this->repo->recordView($this->video->id, $this->user->id);

    expect($this->repo->getTotalViews($this->video->id))->toBe(2);
});

test('get unique viewers counts distinct users', function () {
    $otherUser = User::factory()->create();

    $this->repo->recordView($this->video->id, $this->user->id);
    $this->repo->recordView($this->video->id, $this->user->id);
    $this->repo->recordView($this->video->id, $otherUser->id);
    $this->repo->recordView($this->video->id, null);

    expect($this->repo->getUniqueViewers($this->video->id))->toBe(2);
});

test('get views analytics daily returns grouped rows', function () {
    $this->repo->recordView($this->video->id, $this->user->id);
    $this->repo->recordView($this->video->id, $this->user->id);

    $rows = $this->repo->getViewsAnalytics($this->video->id, 'daily');
    expect($rows)->toHaveCount(1);
    expect($rows[0]['views'])->toBe(2);
});

test('get views analytics supports weekly and monthly periods', function () {
    $this->repo->recordView($this->video->id, $this->user->id);

    $weekly = $this->repo->getViewsAnalytics($this->video->id, 'weekly');
    expect($weekly)->toHaveCount(1);
    expect($weekly[0]['views'])->toBe(1);

    $monthly = $this->repo->getViewsAnalytics($this->video->id, 'monthly');
    expect($monthly)->toHaveCount(1);
    expect($monthly[0]['views'])->toBe(1);
});

test('delete by video removes all views', function () {
    $this->repo->recordView($this->video->id, $this->user->id);

    $this->repo->deleteByVideo($this->video->id);

    expect($this->repo->getTotalViews($this->video->id))->toBe(0);
});

test('find by id returns the view', function () {
    $view = $this->repo->recordView($this->video->id, $this->user->id);

    expect($this->repo->findById($view->id)->id)->toBe($view->id);
    expect($this->repo->findById(99999))->toBeNull();
});

test('query returns a builder', function () {
    expect($this->repo->query())->toBeInstanceOf(Builder::class);
});
