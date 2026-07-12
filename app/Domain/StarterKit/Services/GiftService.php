<?php

namespace App\Domain\StarterKit\Services;

use App\Domain\StarterKit\Policies\GiftPolicy;
use App\Domain\StarterKit\ValueObjects\GiftLimits;
use App\Models\User;

class GiftService
{
    public function __construct(
        private GiftPolicy $giftPolicy
    ) {}

    public function canGiftStarterKit(
        User $gifter,
        User $recipient,
        int $giftAmount,
        GiftLimits $limits
    ): bool {
        $isInDownline = $this->isInDownline($gifter, $recipient);
        $giftsThisMonth = $this->getGiftsThisMonth($gifter->id);
        $giftAmountThisMonth = $this->getGiftAmountThisMonth($gifter->id);
        $walletBalance = $this->getWalletBalance($gifter);

        return $this->giftPolicy->canGift(
            featureEnabled: $limits->giftFeatureEnabled,
            recipientHasStarterKit: $recipient->has_starter_kit,
            isInDownline: $isInDownline,
            walletBalance: $walletBalance,
            giftAmount: $giftAmount,
            giftsThisMonth: $giftsThisMonth,
            giftAmountThisMonth: $giftAmountThisMonth,
            limits: $limits
        );
    }

    public function getGiftDenialReason(
        User $gifter,
        User $recipient,
        int $giftAmount,
        GiftLimits $limits
    ): string {
        $isInDownline = $this->isInDownline($gifter, $recipient);
        $giftsThisMonth = $this->getGiftsThisMonth($gifter->id);
        $giftAmountThisMonth = $this->getGiftAmountThisMonth($gifter->id);
        $walletBalance = $this->getWalletBalance($gifter);

        return $this->giftPolicy->getDenialReason(
            featureEnabled: $limits->giftFeatureEnabled,
            recipientHasStarterKit: $recipient->has_starter_kit,
            isInDownline: $isInDownline,
            walletBalance: $walletBalance,
            giftAmount: $giftAmount,
            giftsThisMonth: $giftsThisMonth,
            giftAmountThisMonth: $giftAmountThisMonth,
            limits: $limits
        );
    }

    private function isInDownline(User $gifter, User $recipient): bool
    {
        // Check if recipient is in gifter's downline using UserNetwork table
        return \App\Models\UserNetwork::where('referrer_id', $gifter->id)
            ->where('user_id', $recipient->id)
            ->where('level', '<=', 7)
            ->exists();
    }

    public function getGiftsThisMonth(int $gifterId): int
    {
        return \DB::table('starter_kit_gifts')
            ->where('gifter_id', $gifterId)
            ->where('status', 'completed')
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count();
    }

    public function getGiftAmountThisMonth(int $gifterId): int
    {
        return (int) \DB::table('starter_kit_gifts')
            ->where('gifter_id', $gifterId)
            ->where('status', 'completed')
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->sum('amount');
    }

    private function getWalletBalance(User $user): int
    {
        $walletService = app(\App\Services\WalletService::class);
        return (int) $walletService->calculateBalance($user);
    }
}
