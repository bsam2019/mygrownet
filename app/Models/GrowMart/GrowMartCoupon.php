<?php

namespace App\Models\GrowMart;

use Illuminate\Database\Eloquent\Model;

class GrowMartCoupon extends Model
{
    protected $table = 'growmart_coupons';

    protected $fillable = [
        'code',
        'type',
        'value',
        'min_order_amount',
        'max_discount',
        'buy_quantity',
        'get_quantity',
        'usage_limit',
        'used_count',
        'starts_at',
        'expires_at',
        'is_active',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'integer',
            'min_order_amount' => 'integer',
            'max_discount' => 'integer',
            'buy_quantity' => 'integer',
            'get_quantity' => 'integer',
            'usage_limit' => 'integer',
            'used_count' => 'integer',
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function isValid(): bool
    {
        if (!$this->is_active) return false;
        if ($this->usage_limit && $this->used_count >= $this->usage_limit) return false;
        if ($this->starts_at && $this->starts_at->isFuture()) return false;
        if ($this->expires_at && $this->expires_at->isPast()) return false;
        return true;
    }

    public function calculateDiscount(int $subtotal): int
    {
        if (!$this->isValid()) return 0;

        if ($this->min_order_amount && $subtotal < $this->min_order_amount) return 0;

        $discount = match ($this->type) {
            'percentage' => (int) round($subtotal * $this->value / 100),
            'bogo' => $this->calculateBogoDiscount($subtotal),
            default => $this->value,
        };

        if ($this->type === 'percentage' && $this->max_discount) {
            $discount = min($discount, $this->max_discount);
        }

        return min($discount, $subtotal);
    }

    private function calculateBogoDiscount(int $subtotal): int
    {
        $buyQty = $this->buy_quantity ?? 1;
        $getQty = $this->get_quantity ?? 1;
        $freeItems = $getQty;
        $totalItemsPerSet = $buyQty + $getQty;
        return (int) round(($this->value / $totalItemsPerSet) * $freeItems);
    }
}
