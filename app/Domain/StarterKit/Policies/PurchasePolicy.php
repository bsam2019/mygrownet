<?php

namespace App\Domain\StarterKit\Policies;

use App\Domain\StarterKit\ValueObjects\Money;

class PurchasePolicy
{
    public function canPurchase(
        bool $alreadyHasStarterKit,
        Money $walletBalance,
        Money $price,
        string $paymentMethod
    ): bool {
        // Cannot purchase if already has starter kit
        if ($alreadyHasStarterKit) {
            return false;
        }

        // For wallet payments, must have sufficient balance
        if ($paymentMethod === 'wallet') {
            return $walletBalance->isGreaterThan($price) || $walletBalance->equals($price);
        }

        // Other payment methods are allowed
        return true;
    }

    public function getPurchaseDenialReason(
        bool $alreadyHasStarterKit,
        Money $walletBalance,
        Money $price,
        string $paymentMethod
    ): string {
        if ($alreadyHasStarterKit) {
            return 'You already have the Starter Kit.';
        }

        if ($paymentMethod === 'wallet' && $walletBalance->isLessThan($price)) {
            $needed = $price->subtract($walletBalance);
            return "Insufficient wallet balance. You need {$needed} more.";
        }

        return 'Purchase not allowed.';
    }
}
