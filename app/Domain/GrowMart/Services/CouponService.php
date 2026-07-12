<?php

namespace App\Domain\GrowMart\Services;

use App\Models\GrowMart\GrowMartCoupon;

class CouponService
{
    public function findByCode(string $code): ?GrowMartCoupon
    {
        return GrowMartCoupon::where('code', strtoupper($code))->first();
    }

    public function validateCoupon(?GrowMartCoupon $coupon, int $subtotal): array
    {
        if (!$coupon) {
            return ['valid' => false, 'message' => 'Coupon not found.'];
        }

        if (!$coupon->isValid()) {
            $msg = match (true) {
                !$coupon->is_active => 'This coupon is no longer active.',
                $coupon->usage_limit && $coupon->used_count >= $coupon->usage_limit => 'This coupon has reached its usage limit.',
                $coupon->starts_at && $coupon->starts_at->isFuture() => 'This coupon is not yet available.',
                $coupon->expires_at && $coupon->expires_at->isPast() => 'This coupon has expired.',
                default => 'This coupon cannot be applied.',
            };
            return ['valid' => false, 'message' => $msg];
        }

        if ($coupon->min_order_amount && $subtotal < $coupon->min_order_amount) {
            $needed = 'K' . number_format($coupon->min_order_amount / 100, 2);
            return ['valid' => false, 'message' => "Minimum order amount of {$needed} required."];
        }

        $discount = $coupon->calculateDiscount($subtotal);

        return [
            'valid' => true,
            'coupon_id' => $coupon->id,
            'code' => $coupon->code,
            'discount' => $discount,
            'discount_formatted' => 'K' . number_format($discount / 100, 2),
            'description' => $coupon->description,
        ];
    }

    public function incrementUsage(GrowMartCoupon $coupon): void
    {
        $coupon->increment('used_count');
    }
}
