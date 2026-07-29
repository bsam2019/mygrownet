<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\GrowMart\Services;

use App\Domain\GrowMart\Repositories\CouponRepositoryInterface;
use App\Domain\GrowMart\Services\CouponService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class CouponServiceTest extends TestCase
{
    private CouponRepositoryInterface $couponRepo;
    private CouponService $service;

    protected function setUp(): void
    {
        $this->couponRepo = $this->createStub(CouponRepositoryInterface::class);
        $this->service = new CouponService($this->couponRepo);
    }

    private function validCoupon(array $overrides = []): array
    {
        return array_merge([
            'id' => 1,
            'code' => 'SAVE10',
            'type' => 'fixed',
            'value' => 500,
            'is_active' => true,
            'usage_limit' => null,
            'used_count' => 0,
            'starts_at' => '2026-01-01 00:00:00',
            'expires_at' => '2026-12-31 23:59:59',
            'min_order_amount' => null,
            'max_discount' => null,
            'buy_quantity' => null,
            'get_quantity' => null,
            'description' => 'Save K5',
        ], $overrides);
    }

    #[Test]
    public function find_by_code_delegates_to_repository(): void
    {
        $expected = $this->validCoupon();
        $this->couponRepo = $this->createMock(CouponRepositoryInterface::class);
        $this->couponRepo->expects($this->once())
            ->method('findByCode')
            ->with('SAVE10')
            ->willReturn($expected);
        $this->service = new CouponService($this->couponRepo);

        $this->assertSame($expected, $this->service->findByCode('SAVE10'));
    }

    #[Test]
    public function find_by_code_returns_null_when_not_found(): void
    {
        $this->couponRepo = $this->createMock(CouponRepositoryInterface::class);
        $this->couponRepo->expects($this->once())
            ->method('findByCode')
            ->with('NONEXISTENT')
            ->willReturn(null);
        $this->service = new CouponService($this->couponRepo);

        $this->assertNull($this->service->findByCode('NONEXISTENT'));
    }

    #[Test]
    public function validate_coupon_returns_invalid_when_coupon_null(): void
    {
        $result = $this->service->validateCoupon(null, 10000);
        $this->assertFalse($result['valid']);
        $this->assertEquals('Coupon not found.', $result['message']);
    }

    #[Test]
    public function validate_coupon_rejects_inactive_coupon(): void
    {
        $result = $this->service->validateCoupon($this->validCoupon(['is_active' => false]), 10000);
        $this->assertFalse($result['valid']);
        $this->assertEquals('This coupon is no longer active.', $result['message']);
    }

    #[Test]
    public function validate_coupon_rejects_used_up_coupon(): void
    {
        $result = $this->service->validateCoupon(
            $this->validCoupon(['usage_limit' => 5, 'used_count' => 5]),
            10000,
        );
        $this->assertFalse($result['valid']);
        $this->assertStringContainsString('usage limit', $result['message']);
    }

    #[Test]
    public function validate_coupon_rejects_future_coupon(): void
    {
        $result = $this->service->validateCoupon(
            $this->validCoupon(['starts_at' => '2099-01-01 00:00:00']),
            10000,
        );
        $this->assertFalse($result['valid']);
        $this->assertStringContainsString('not yet available', $result['message']);
    }

    #[Test]
    public function validate_coupon_rejects_expired_coupon(): void
    {
        $result = $this->service->validateCoupon(
            $this->validCoupon(['expires_at' => '2024-01-01 00:00:00']),
            10000,
        );
        $this->assertFalse($result['valid']);
        $this->assertStringContainsString('expired', $result['message']);
    }

    #[Test]
    public function validate_coupon_rejects_subtotal_below_minimum(): void
    {
        $coupon = $this->validCoupon(['min_order_amount' => 5000]);
        $result = $this->service->validateCoupon($coupon, 3000);
        $this->assertFalse($result['valid']);
        $this->assertStringContainsString('Minimum order amount', $result['message']);
    }

    #[Test]
    public function validate_coupon_accepts_subtotal_equal_to_minimum(): void
    {
        $coupon = $this->validCoupon(['min_order_amount' => 5000]);
        $result = $this->service->validateCoupon($coupon, 5000);
        $this->assertTrue($result['valid']);
    }

    #[Test]
    public function validate_coupon_returns_valid_for_fixed_coupon(): void
    {
        $coupon = $this->validCoupon(['type' => 'fixed', 'value' => 1000]);
        $result = $this->service->validateCoupon($coupon, 10000);
        $this->assertTrue($result['valid']);
        $this->assertEquals(1, $result['coupon_id']);
        $this->assertEquals('SAVE10', $result['code']);
        $this->assertEquals(1000, $result['discount']);
        $this->assertEquals('K10.00', $result['discount_formatted']);
        $this->assertEquals('Save K5', $result['description']);
    }

    #[Test]
    public function validate_coupon_calculates_percentage_discount(): void
    {
        $coupon = $this->validCoupon(['type' => 'percentage', 'value' => 20]);
        $result = $this->service->validateCoupon($coupon, 10000);
        $this->assertTrue($result['valid']);
        $this->assertEquals(2000, $result['discount']);
    }

    #[Test]
    public function validate_coupon_caps_percentage_at_max_discount(): void
    {
        $coupon = $this->validCoupon([
            'type' => 'percentage',
            'value' => 50,
            'max_discount' => 2000,
        ]);
        $result = $this->service->validateCoupon($coupon, 10000);
        $this->assertTrue($result['valid']);
        $this->assertEquals(2000, $result['discount']);
    }

    #[Test]
    public function validate_coupon_does_not_exceed_subtotal(): void
    {
        $coupon = $this->validCoupon(['type' => 'fixed', 'value' => 10000]);
        $result = $this->service->validateCoupon($coupon, 5000);
        $this->assertTrue($result['valid']);
        $this->assertEquals(5000, $result['discount']);
    }

    #[Test]
    public function validate_coupon_handles_bogo_type(): void
    {
        $coupon = $this->validCoupon([
            'type' => 'bogo',
            'value' => 0,
            'buy_quantity' => 2,
            'get_quantity' => 1,
        ]);
        $result = $this->service->validateCoupon($coupon, 3000);
        $this->assertTrue($result['valid']);
    }

    #[Test]
    public function validate_coupon_handles_unknown_type_as_fixed(): void
    {
        $coupon = $this->validCoupon(['type' => 'unknown_type', 'value' => 750]);
        $result = $this->service->validateCoupon($coupon, 10000);
        $this->assertTrue($result['valid']);
        $this->assertEquals(750, $result['discount']);
    }

    #[Test]
    public function validate_coupon_without_starts_at_is_valid(): void
    {
        $coupon = $this->validCoupon(['starts_at' => null]);
        $result = $this->service->validateCoupon($coupon, 10000);
        $this->assertTrue($result['valid']);
    }

    #[Test]
    public function validate_coupon_without_expires_at_is_valid(): void
    {
        $coupon = $this->validCoupon(['expires_at' => null]);
        $result = $this->service->validateCoupon($coupon, 10000);
        $this->assertTrue($result['valid']);
    }

    #[Test]
    public function validate_coupon_without_usage_limit_is_valid(): void
    {
        $coupon = $this->validCoupon(['usage_limit' => null, 'used_count' => 999]);
        $result = $this->service->validateCoupon($coupon, 10000);
        $this->assertTrue($result['valid']);
    }

    #[Test]
    public function increment_usage_delegates(): void
    {
        $coupon = $this->validCoupon(['id' => 5]);
        $this->couponRepo = $this->createMock(CouponRepositoryInterface::class);
        $this->couponRepo->expects($this->once())
            ->method('incrementUsage')
            ->with(5);
        $this->service = new CouponService($this->couponRepo);

        $this->service->incrementUsage($coupon);
    }

    #[Test]
    public function validate_coupon_missing_is_active_defaults_to_false(): void
    {
        $coupon = $this->validCoupon();
        unset($coupon['is_active']);
        $result = $this->service->validateCoupon($coupon, 10000);
        $this->assertFalse($result['valid']);
    }

    #[Test]
    public function validate_coupon_with_zero_value_is_valid(): void
    {
        $coupon = $this->validCoupon(['type' => 'fixed', 'value' => 0]);
        $result = $this->service->validateCoupon($coupon, 10000);
        $this->assertTrue($result['valid']);
        $this->assertEquals(0, $result['discount']);
    }
}
