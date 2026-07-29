<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\GrowMart\Services;

use App\Domain\GrowMart\Repositories\WishlistRepositoryInterface;
use App\Domain\GrowMart\Services\WishlistService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class WishlistServiceTest extends TestCase
{
    private WishlistRepositoryInterface $wishlistRepo;
    private WishlistService $service;

    protected function setUp(): void
    {
        $this->wishlistRepo = $this->createStub(WishlistRepositoryInterface::class);
        $this->service = new WishlistService($this->wishlistRepo);
    }

    #[Test]
    public function get_wishlist_delegates_to_repository(): void
    {
        $expected = ['data' => [['id' => 1]], 'total' => 1];
        $this->wishlistRepo = $this->createMock(WishlistRepositoryInterface::class);
        $this->wishlistRepo->expects($this->once())
            ->method('findByUser')
            ->with(42, 10)
            ->willReturn($expected);
        $this->service = new WishlistService($this->wishlistRepo);

        $this->assertSame($expected, $this->service->getWishlist(42, 10));
    }

    #[Test]
    public function get_wishlist_uses_default_per_page(): void
    {
        $expected = ['data' => []];
        $this->wishlistRepo = $this->createMock(WishlistRepositoryInterface::class);
        $this->wishlistRepo->expects($this->once())
            ->method('findByUser')
            ->with(1, 20)
            ->willReturn($expected);
        $this->service = new WishlistService($this->wishlistRepo);

        $this->assertSame($expected, $this->service->getWishlist(1));
    }

    #[Test]
    public function is_wishlisted_delegates(): void
    {
        $this->wishlistRepo = $this->createMock(WishlistRepositoryInterface::class);
        $this->wishlistRepo->expects($this->once())
            ->method('isWishlisted')
            ->with(42, 5)
            ->willReturn(true);
        $this->service = new WishlistService($this->wishlistRepo);

        $this->assertTrue($this->service->isWishlisted(42, 5));
    }

    #[Test]
    public function is_wishlisted_returns_false(): void
    {
        $this->wishlistRepo = $this->createMock(WishlistRepositoryInterface::class);
        $this->wishlistRepo->expects($this->once())
            ->method('isWishlisted')
            ->willReturn(false);
        $this->service = new WishlistService($this->wishlistRepo);

        $this->assertFalse($this->service->isWishlisted(42, 99));
    }

    #[Test]
    public function get_wishlist_product_ids_delegates(): void
    {
        $expected = [1, 2, 3];
        $this->wishlistRepo = $this->createMock(WishlistRepositoryInterface::class);
        $this->wishlistRepo->expects($this->once())
            ->method('findProductIdsByUser')
            ->with(42)
            ->willReturn($expected);
        $this->service = new WishlistService($this->wishlistRepo);

        $this->assertSame($expected, $this->service->getWishlistProductIds(42));
    }

    #[Test]
    public function toggle_adds_when_not_wishlisted(): void
    {
        $this->wishlistRepo = $this->createMock(WishlistRepositoryInterface::class);
        $this->wishlistRepo->expects($this->once())
            ->method('findByUserAndProduct')
            ->with(42, 5)
            ->willReturn(null);
        $this->wishlistRepo->expects($this->once())
            ->method('add')
            ->with(42, 5)
            ->willReturn(['id' => 1]);
        $this->service = new WishlistService($this->wishlistRepo);

        $result = $this->service->toggle(42, 5);

        $this->assertTrue($result['wishlisted']);
        $this->assertEquals('Added to wishlist', $result['message']);
    }

    #[Test]
    public function toggle_removes_when_already_wishlisted(): void
    {
        $this->wishlistRepo = $this->createMock(WishlistRepositoryInterface::class);
        $this->wishlistRepo->expects($this->once())
            ->method('findByUserAndProduct')
            ->with(42, 5)
            ->willReturn(['id' => 10]);
        $this->wishlistRepo->expects($this->once())
            ->method('remove')
            ->with(42, 5);
        $this->service = new WishlistService($this->wishlistRepo);

        $result = $this->service->toggle(42, 5);

        $this->assertFalse($result['wishlisted']);
        $this->assertEquals('Removed from wishlist', $result['message']);
    }

    #[Test]
    public function remove_delegates_to_repository(): void
    {
        $this->wishlistRepo = $this->createMock(WishlistRepositoryInterface::class);
        $this->wishlistRepo->expects($this->once())
            ->method('remove')
            ->with(42, 5);
        $this->service = new WishlistService($this->wishlistRepo);

        $this->service->remove(42, 5);
    }

    #[Test]
    public function toggle_with_multiple_users_isolates_correctly(): void
    {
        $this->wishlistRepo = $this->createMock(WishlistRepositoryInterface::class);

        $invocations = new \stdClass();
        $invocations->count = 0;

        $this->wishlistRepo->method('findByUserAndProduct')
            ->willReturnCallback(function ($userId, $productId) use ($invocations) {
                if ($userId === 1 && $productId === 10) {
                    return null;
                }
                if ($userId === 2 && $productId === 10) {
                    return ['id' => 99];
                }
                return null;
            });

        $this->wishlistRepo->expects($this->once())
            ->method('add')
            ->with(1, 10);
        $this->wishlistRepo->expects($this->once())
            ->method('remove')
            ->with(2, 10);

        $this->service = new WishlistService($this->wishlistRepo);

        $result1 = $this->service->toggle(1, 10);
        $this->assertTrue($result1['wishlisted']);

        $result2 = $this->service->toggle(2, 10);
        $this->assertFalse($result2['wishlisted']);
    }
}
