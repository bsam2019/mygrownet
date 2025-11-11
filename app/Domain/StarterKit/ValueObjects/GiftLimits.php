<?php

namespace App\Domain\StarterKit\ValueObjects;

class GiftLimits
{
    public function __construct(
        public readonly int $maxGiftsPerMonth,
        public readonly int $maxGiftAmountPerMonth,
        public readonly int $minWalletBalanceToGift,
        public readonly bool $giftFeatureEnabled,
        public readonly float $giftFeePercentage
    ) {}

    public static function fromSettings(array $settings): self
    {
        return new self(
            maxGiftsPerMonth: $settings['max_gifts_per_month'] ?? 5,
            maxGiftAmountPerMonth: $settings['max_gift_amount_per_month'] ?? 5000,
            minWalletBalanceToGift: $settings['min_wallet_balance_to_gift'] ?? 1000,
            giftFeatureEnabled: $settings['gift_feature_enabled'] ?? true,
            giftFeePercentage: $settings['gift_fee_percentage'] ?? 0
        );
    }

    public function calculateFee(int $amount): int
    {
        return (int) ($amount * $this->giftFeePercentage / 100);
    }

    public function getTotalCost(int $amount): int
    {
        return $amount + $this->calculateFee($amount);
    }
}
