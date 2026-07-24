<?php

namespace App\Domain\GrowMart\Services;

use App\Domain\GrowMart\Repositories\CouponRepositoryInterface;

class CouponService
{
    public function __construct(
        private readonly CouponRepositoryInterface $couponRepository,
    ) {}

    public function findByCode(string $code): ?array
    {
        return $this->couponRepository->findByCode($code);
    }

    public function validateCoupon(?array $coupon, int $subtotal): array
    {
        if (!$coupon) {
            return ['valid' => false, 'message' => 'Coupon not found.'];
        }

        if (!$this->isValid($coupon)) {
            $msg = match (true) {
                !($coupon['is_active'] ?? false) => 'This coupon is no longer active.',
                isset($coupon['usage_limit']) && ($coupon['used_count'] ?? 0) >= $coupon['usage_limit'] => 'This coupon has reached its usage limit.',
                isset($coupon['starts_at']) && $coupon['starts_at'] && \Carbon\Carbon::parse($coupon['starts_at'])->isFuture() => 'This coupon is not yet available.',
                isset($coupon['expires_at']) && $coupon['expires_at'] && \Carbon\Carbon::parse($coupon['expires_at'])->isPast() => 'This coupon has expired.',
                default => 'This coupon cannot be applied.',
            };
            return ['valid' => false, 'message' => $msg];
        }

        if (($coupon['min_order_amount'] ?? 0) && $subtotal < $coupon['min_order_amount']) {
            $needed = 'K' . number_format($coupon['min_order_amount'] / 100, 2);
            return ['valid' => false, 'message' => "Minimum order amount of {$needed} required."];
        }

        $discount = $this->calculateDiscount($coupon, $subtotal);

        return [
            'valid' => true,
            'coupon_id' => $coupon['id'],
            'code' => $coupon['code'],
            'discount' => $discount,
            'discount_formatted' => 'K' . number_format($discount / 100, 2),
            'description' => $coupon['description'] ?? '',
        ];
    }

    public function incrementUsage(array $coupon): void
    {
        $this->couponRepository->incrementUsage($coupon['id']);
    }

    private function isValid(array $coupon): bool
    {
        if (!($coupon['is_active'] ?? false)) return false;
        if (($coupon['usage_limit'] ?? 0) && ($coupon['used_count'] ?? 0) >= $coupon['usage_limit']) return false;
        if (isset($coupon['starts_at']) && $coupon['starts_at'] && \Carbon\Carbon::parse($coupon['starts_at'])->isFuture()) return false;
        if (isset($coupon['expires_at']) && $coupon['expires_at'] && \Carbon\Carbon::parse($coupon['expires_at'])->isPast()) return false;
        return true;
    }

    private function calculateDiscount(array $coupon, int $subtotal): int
    {
        if (!$this->isValid($coupon)) return 0;

        if (($coupon['min_order_amount'] ?? 0) && $subtotal < $coupon['min_order_amount']) return 0;

        $discount = match ($coupon['type'] ?? 'fixed') {
            'percentage' => (int) round($subtotal * ($coupon['value'] ?? 0) / 100),
            'bogo' => $this->calculateBogoDiscount($coupon, $subtotal),
            default => $coupon['value'] ?? 0,
        };

        if (($coupon['type'] ?? '') === 'percentage' && ($coupon['max_discount'] ?? 0)) {
            $discount = min($discount, $coupon['max_discount']);
        }

        return min($discount, $subtotal);
    }

    private function calculateBogoDiscount(array $coupon, int $subtotal): int
    {
        $buyQty = $coupon['buy_quantity'] ?? 1;
        $getQty = $coupon['get_quantity'] ?? 1;
        $freeItems = $getQty;
        $totalItemsPerSet = $buyQty + $getQty;
        return (int) round(($coupon['value'] / $totalItemsPerSet) * $freeItems);
    }
}
