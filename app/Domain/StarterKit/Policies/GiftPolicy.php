<?php

namespace App\Domain\StarterKit\Policies;

use App\Domain\StarterKit\ValueObjects\GiftLimits;

class GiftPolicy
{
    public function canGift(
        bool $featureEnabled,
        bool $recipientHasStarterKit,
        bool $isInDownline,
        int $walletBalance,
        int $giftAmount,
        int $giftsThisMonth,
        int $giftAmountThisMonth,
        GiftLimits $limits
    ): bool {
        return $featureEnabled
            && !$recipientHasStarterKit
            && $isInDownline
            && $walletBalance >= $limits->minWalletBalanceToGift
            && $walletBalance >= $limits->getTotalCost($giftAmount)
            && $giftsThisMonth < $limits->maxGiftsPerMonth
            && ($giftAmountThisMonth + $giftAmount) <= $limits->maxGiftAmountPerMonth;
    }

    public function getDenialReason(
        bool $featureEnabled,
        bool $recipientHasStarterKit,
        bool $isInDownline,
        int $walletBalance,
        int $giftAmount,
        int $giftsThisMonth,
        int $giftAmountThisMonth,
        GiftLimits $limits
    ): string {
        if (!$featureEnabled) {
            return 'Gift feature is currently disabled.';
        }

        if ($recipientHasStarterKit) {
            return 'This member already has a starter kit.';
        }

        if (!$isInDownline) {
            return 'You can only gift to members in your downline.';
        }

        if ($walletBalance < $limits->minWalletBalanceToGift) {
            return "Minimum wallet balance of K{$limits->minWalletBalanceToGift} required to gift.";
        }

        $totalCost = $limits->getTotalCost($giftAmount);
        if ($walletBalance < $totalCost) {
            return "Insufficient wallet balance. Need K{$totalCost} (including fees).";
        }

        if ($giftsThisMonth >= $limits->maxGiftsPerMonth) {
            return "Monthly gift limit reached ({$limits->maxGiftsPerMonth} gifts).";
        }

        $remainingAmount = $limits->maxGiftAmountPerMonth - $giftAmountThisMonth;
        if ($giftAmount > $remainingAmount) {
            return "Monthly gift amount limit reached. Remaining: K{$remainingAmount}.";
        }

        return 'Gift not allowed.';
    }
}
