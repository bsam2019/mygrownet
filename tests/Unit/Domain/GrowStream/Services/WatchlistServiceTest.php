<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\GrowStream\Services;

use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\Watchlist as EloquentWatchlist;
use App\Domain\GrowStream\Repositories\WatchlistRepositoryInterface;
use App\Domain\GrowStream\Services\WatchlistService;
use Illuminate\Pagination\LengthAwarePaginator;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class WatchlistServiceTest extends TestCase
{
    private WatchlistRepositoryInterface $watchlistRepo;
    private WatchlistService $service;

    protected function setUp(): void
    {
        $this->watchlistRepo = $this->createStub(WatchlistRepositoryInterface::class);
        $this->service = new WatchlistService($this->watchlistRepo);
    }

    private function mockPaginator(array $data): LengthAwarePaginator
    {
        $mock = $this->createMock(LengthAwarePaginator::class);
        $mock->method('toArray')->willReturn($data);
        return $mock;
    }

    private function mockWatchlist(array $data): EloquentWatchlist
    {
        $mock = $this->createMock(EloquentWatchlist::class);
        $mock->method('toArray')->willReturn($data);
        return $mock;
    }

    // ---- addToWatchlist ----

    #[Test]
    public function add_to_watchlist_success(): void
    {
        $expected = ['id' => 1, 'user_id' => 42, 'video_id' => 5];
        $watchlistMock = $this->mockWatchlist($expected);

        $this->watchlistRepo = $this->createMock(WatchlistRepositoryInterface::class);
        $this->watchlistRepo->expects($this->once())
            ->method('isInWatchlist')
            ->with(42, 5)
            ->willReturn(false);
        $this->watchlistRepo->expects($this->once())
            ->method('addToWatchlist')
            ->with(42, 5, null)
            ->willReturn($watchlistMock);

        $this->service = new WatchlistService($this->watchlistRepo);

        $result = $this->service->addToWatchlist(42, 5);
        $this->assertSame($expected, $result);
    }

    #[Test]
    public function add_to_watchlist_with_notes(): void
    {
        $expected = ['id' => 1, 'notes' => 'Must watch!'];
        $watchlistMock = $this->mockWatchlist($expected);

        $this->watchlistRepo = $this->createMock(WatchlistRepositoryInterface::class);
        $this->watchlistRepo->expects($this->once())
            ->method('isInWatchlist')
            ->with(42, 5)
            ->willReturn(false);
        $this->watchlistRepo->expects($this->once())
            ->method('addToWatchlist')
            ->with(42, 5, 'Must watch!')
            ->willReturn($watchlistMock);

        $this->service = new WatchlistService($this->watchlistRepo);

        $result = $this->service->addToWatchlist(42, 5, 'Must watch!');
        $this->assertSame($expected, $result);
    }

    #[Test]
    public function add_to_watchlist_throws_when_already_in_watchlist(): void
    {
        $this->watchlistRepo = $this->createMock(WatchlistRepositoryInterface::class);
        $this->watchlistRepo->expects($this->once())
            ->method('isInWatchlist')
            ->with(42, 5)
            ->willReturn(true);
        $this->watchlistRepo->expects($this->never())
            ->method('addToWatchlist');

        $this->service = new WatchlistService($this->watchlistRepo);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Video is already in your watchlist');
        $this->service->addToWatchlist(42, 5);
    }

    // ---- removeFromWatchlist ----

    #[Test]
    public function remove_from_watchlist_delegates(): void
    {
        $this->watchlistRepo = $this->createMock(WatchlistRepositoryInterface::class);
        $this->watchlistRepo->expects($this->once())
            ->method('removeFromWatchlist')
            ->with(42, 5)
            ->willReturn(true);

        $this->service = new WatchlistService($this->watchlistRepo);

        $this->service->removeFromWatchlist(42, 5);
    }

    // ---- getUserWatchlist ----

    #[Test]
    public function get_user_watchlist_delegates(): void
    {
        $expected = ['data' => [['id' => 1]], 'total' => 1];
        $paginator = $this->mockPaginator($expected);

        $this->watchlistRepo = $this->createMock(WatchlistRepositoryInterface::class);
        $this->watchlistRepo->expects($this->once())
            ->method('getUserWatchlist')
            ->with(42, 15)
            ->willReturn($paginator);

        $this->service = new WatchlistService($this->watchlistRepo);

        $result = $this->service->getUserWatchlist(42, 1, 15);
        $this->assertSame($expected, $result);
    }

    #[Test]
    public function get_user_watchlist_uses_default_per_page(): void
    {
        $expected = ['data' => []];
        $paginator = $this->mockPaginator($expected);

        $this->watchlistRepo = $this->createMock(WatchlistRepositoryInterface::class);
        $this->watchlistRepo->expects($this->once())
            ->method('getUserWatchlist')
            ->with(42, 20)
            ->willReturn($paginator);

        $this->service = new WatchlistService($this->watchlistRepo);

        $result = $this->service->getUserWatchlist(42);
        $this->assertSame($expected, $result);
    }

    // ---- isInWatchlist ----

    #[Test]
    public function is_in_watchlist_returns_true(): void
    {
        $this->watchlistRepo = $this->createMock(WatchlistRepositoryInterface::class);
        $this->watchlistRepo->expects($this->once())
            ->method('isInWatchlist')
            ->with(42, 5)
            ->willReturn(true);

        $this->service = new WatchlistService($this->watchlistRepo);

        $this->assertTrue($this->service->isInWatchlist(42, 5));
    }

    #[Test]
    public function is_in_watchlist_returns_false(): void
    {
        $this->watchlistRepo = $this->createMock(WatchlistRepositoryInterface::class);
        $this->watchlistRepo->expects($this->once())
            ->method('isInWatchlist')
            ->with(42, 99)
            ->willReturn(false);

        $this->service = new WatchlistService($this->watchlistRepo);

        $this->assertFalse($this->service->isInWatchlist(42, 99));
    }

    // ---- getWatchlistCount ----

    #[Test]
    public function get_watchlist_count_delegates(): void
    {
        $this->watchlistRepo = $this->createMock(WatchlistRepositoryInterface::class);
        $this->watchlistRepo->expects($this->once())
            ->method('count')
            ->with(42)
            ->willReturn(5);

        $this->service = new WatchlistService($this->watchlistRepo);

        $this->assertSame(5, $this->service->getWatchlistCount(42));
    }

    // ---- clearWatchlist ----

    #[Test]
    public function clear_watchlist_delegates(): void
    {
        $this->watchlistRepo = $this->createMock(WatchlistRepositoryInterface::class);
        $this->watchlistRepo->expects($this->once())
            ->method('deleteAll')
            ->with(42);

        $this->service = new WatchlistService($this->watchlistRepo);

        $this->service->clearWatchlist(42);
    }

    #[Test]
    public function clear_watchlist_handles_multiple_users(): void
    {
        $this->watchlistRepo = $this->createMock(WatchlistRepositoryInterface::class);

        $matcher = $this->exactly(2);
        $this->watchlistRepo->expects($matcher)
            ->method('deleteAll')
            ->willReturnCallback(function (int $userId) use ($matcher) {
                match ($matcher->numberOfInvocations()) {
                    1 => $this->assertSame(1, $userId),
                    2 => $this->assertSame(2, $userId),
                };
            });

        $this->service = new WatchlistService($this->watchlistRepo);

        $this->service->clearWatchlist(1);
        $this->service->clearWatchlist(2);
    }
}