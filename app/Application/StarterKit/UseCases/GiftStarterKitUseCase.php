<?php

namespace App\Application\StarterKit\UseCases;

use App\Domain\StarterKit\Services\GiftService;
use App\Domain\StarterKit\ValueObjects\GiftLimits;
use App\Infrastructure\Persistence\Eloquent\StarterKit\StarterKitGiftModel;
use App\Infrastructure\Persistence\Eloquent\Settings\GiftSettingsModel;
use App\Models\User;
use App\Services\StarterKitService;
use App\Domain\Wallet\Services\UnifiedWalletService;
use App\Domain\Announcement\Services\EventBasedAnnouncementService;
use Illuminate\Support\Facades\DB;

class GiftStarterKitUseCase
{
    public function __construct(
        private GiftService $giftService,
        private StarterKitService $starterKitService,
        private UnifiedWalletService $walletService,
        private EventBasedAnnouncementService $announcementService
    ) {}

    public function execute(int $gifterId, int $recipientId, string $tier): array
    {
        // Get users
        $gifter = User::findOrFail($gifterId);
        $recipient = User::findOrFail($recipientId);

        // Get gift settings
        $settingsArray = GiftSettingsModel::get();
        $limits = GiftLimits::fromSettings($settingsArray);

        // Get gift amount
        $giftAmount = $tier === 'premium' ? 1000 : 500;

        // Check if gift is allowed
        if (!$this->giftService->canGiftStarterKit($gifter, $recipient, $giftAmount, $limits)) {
            $reason = $this->giftService->getGiftDenialReason($gifter, $recipient, $giftAmount, $limits);
            throw new \InvalidArgumentException($reason);
        }

        // Process gift in transaction
        return DB::transaction(function () use ($gifter, $recipient, $tier, $giftAmount, $limits) {
            // Calculate total cost (including fee)
            $totalCost = $limits->getTotalCost($giftAmount);

            // Create gift record
            $gift = StarterKitGiftModel::create([
                'gifter_id' => $gifter->id,
                'recipient_id' => $recipient->id,
                'tier' => $tier,
                'amount' => $totalCost,
                'status' => 'pending',
            ]);

            // Deduct from gifter's wallet
            DB::table('transactions')->insert([
                'user_id' => $gifter->id,
                'transaction_type' => 'starter_kit_gift',
                'amount' => -$totalCost, // Negative for debit
                'reference_number' => 'GIFT-' . strtoupper(uniqid()),
                'description' => "Gifted {$tier} starter kit to {$recipient->name}",
                'status' => 'completed',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            // Clear gifter's wallet cache
            $this->walletService->clearCache($gifter);

            // Purchase starter kit for recipient (using 'gift' payment method)
            $purchase = $this->starterKitService->purchaseStarterKit(
                $recipient,
                'gift',
                'GIFT-' . $gift->id,
                $tier
            );

            // Update gift record
            $gift->update([
                'purchase_id' => $purchase->id,
                'status' => 'completed',
            ]);

            // Update purchase to track gifter
            $purchase->update(['gifted_by' => $gifter->id]);

            // Create announcement for recipient
            $this->announcementService->createStarterKitGift(
                $recipient->id,
                $gifter->name,
                $tier
            );

            return [
                'success' => true,
                'gift' => $gift,
                'purchase' => $purchase,
                'message' => "Starter kit gifted to {$recipient->name} successfully!",
            ];
        });
    }

    public function getGiftLimits(int $userId): array
    {
        $user = User::findOrFail($userId);
        $settingsArray = GiftSettingsModel::get();
        $limits = GiftLimits::fromSettings($settingsArray);

        $giftsThisMonth = $this->giftService->getGiftsThisMonth($userId);
        $giftAmountThisMonth = $this->giftService->getGiftAmountThisMonth($userId);
        $walletBalance = $this->walletService->calculateBalance($user);

        // Debug logging
        \Log::info('Gift Limits Debug', [
            'user_id' => $userId,
            'wallet_balance_raw' => $walletBalance,
            'wallet_balance_type' => gettype($walletBalance),
            'wallet_balance_int' => (int) round($walletBalance),
        ]);

        return [
            'feature_enabled' => $limits->giftFeatureEnabled,
            'max_gifts_per_month' => $limits->maxGiftsPerMonth,
            'max_gift_amount_per_month' => $limits->maxGiftAmountPerMonth,
            'min_wallet_balance_required' => $limits->minWalletBalanceToGift,
            'gift_fee_percentage' => $limits->giftFeePercentage,
            'gifts_used_this_month' => $giftsThisMonth,
            'amount_used_this_month' => $giftAmountThisMonth,
            'remaining_gifts' => max(0, $limits->maxGiftsPerMonth - $giftsThisMonth),
            'remaining_amount' => max(0, $limits->maxGiftAmountPerMonth - $giftAmountThisMonth),
            'current_wallet_balance' => (int) round($walletBalance),
        ];
    }
}
