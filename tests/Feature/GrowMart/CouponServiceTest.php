<?php

namespace Tests\Feature\GrowMart;

use App\Domain\GrowMart\Services\CouponService;
use App\Models\GrowMart\GrowMartCoupon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CouponServiceTest extends TestCase
{
    use RefreshDatabase;

    private CouponService $couponService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->couponService = app(CouponService::class);
    }

    #[Test]
    public function finds_coupon_by_code(): void
    {
        $coupon = GrowMartCoupon::create([
            'code' => 'SAVE10',
            'type' => 'percentage',
            'value' => 1000,
            'is_active' => true,
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addMonth(),
        ]);
        $found = $this->couponService->findByCode('SAVE10');
        $this->assertNotNull($found);
        $this->assertEquals($coupon->id, $found['id']);
    }

    #[Test]
    public function returns_null_for_unknown_code(): void
    {
        $this->assertNull($this->couponService->findByCode('NONEXISTENT'));
    }

    #[Test]
    public function validates_active_percentage_coupon(): void
    {
        $coupon = GrowMartCoupon::create([
            'code' => 'PCT20',
            'type' => 'percentage',
            'value' => 20,
            'is_active' => true,
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addMonth(),
        ]);
        $data = $coupon->toArray();
        $result = $this->couponService->validateCoupon($data, 10000);
        $this->assertTrue($result['valid']);
        $this->assertEquals(2000, $result['discount']);
    }

    #[Test]
    public function validates_expired_coupon_as_invalid(): void
    {
        $coupon = GrowMartCoupon::create([
            'code' => 'EXPIRED',
            'type' => 'fixed',
            'value' => 500,
            'is_active' => true,
            'starts_at' => now()->subMonths(2),
            'expires_at' => now()->subMonth(),
        ]);
        $result = $this->couponService->validateCoupon($coupon->toArray(), 10000);
        $this->assertFalse($result['valid']);
    }

    #[Test]
    public function validates_inactive_coupon_as_invalid(): void
    {
        $coupon = GrowMartCoupon::create([
            'code' => 'INACTIVE',
            'type' => 'fixed',
            'value' => 500,
            'is_active' => false,
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addMonth(),
        ]);
        $result = $this->couponService->validateCoupon($coupon->toArray(), 10000);
        $this->assertFalse($result['valid']);
    }

    #[Test]
    public function validates_min_order_amount(): void
    {
        $coupon = GrowMartCoupon::create([
            'code' => 'MINAMT',
            'type' => 'fixed',
            'value' => 500,
            'min_order_amount' => 5000,
            'is_active' => true,
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addMonth(),
        ]);
        $result = $this->couponService->validateCoupon($coupon->toArray(), 3000);
        $this->assertFalse($result['valid']);
    }

    #[Test]
    public function respects_max_discount(): void
    {
        $coupon = GrowMartCoupon::create([
            'code' => 'MAXDISC',
            'type' => 'percentage',
            'value' => 50,
            'max_discount' => 2000,
            'is_active' => true,
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addMonth(),
        ]);
        $result = $this->couponService->validateCoupon($coupon->toArray(), 10000);
        $this->assertTrue($result['valid']);
        $this->assertEquals(2000, $result['discount']);
    }

    #[Test]
    public function validates_usage_limit(): void
    {
        $coupon = GrowMartCoupon::create([
            'code' => 'USAGELIM',
            'type' => 'fixed',
            'value' => 500,
            'usage_limit' => 5,
            'used_count' => 5,
            'is_active' => true,
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addMonth(),
        ]);
        $result = $this->couponService->validateCoupon($coupon->toArray(), 10000);
        $this->assertFalse($result['valid']);
    }

    #[Test]
    public function increments_usage_count(): void
    {
        $coupon = GrowMartCoupon::create([
            'code' => 'INCR',
            'type' => 'fixed',
            'value' => 100,
            'is_active' => true,
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addMonth(),
        ]);
        $this->couponService->incrementUsage($coupon->toArray());
        $this->assertEquals(1, $coupon->fresh()->used_count);
    }

    #[Test]
    public function calculates_bogo_discount(): void
    {
        $coupon = GrowMartCoupon::create([
            'code' => 'BOGO',
            'type' => 'bogo',
            'value' => 0,
            'buy_quantity' => 2,
            'get_quantity' => 1,
            'is_active' => true,
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addMonth(),
        ]);
        $result = $this->couponService->validateCoupon($coupon->toArray(), 3000);
        $this->assertTrue($result['valid']);
    }
}
