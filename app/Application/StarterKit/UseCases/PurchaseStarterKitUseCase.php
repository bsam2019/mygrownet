<?php

namespace App\Application\StarterKit\UseCases;

use App\Domain\StarterKit\Entities\StarterKitPurchase;
use App\Domain\StarterKit\Policies\PurchasePolicy;
use App\Domain\StarterKit\ValueObjects\Money;
use App\Domain\StarterKit\ValueObjects\ShopCredit;
use App\Domain\Library\ValueObjects\AccessPeriod;
use App\Models\User;
use App\Services\StarterKitService;
use InvalidArgumentException;

class PurchaseStarterKitUseCase
{
    private const PRICE_KWACHA = 500;
    private const SHOP_CREDIT_KWACHA = 100;
    private const LP_BONUS = 37.5;

    public function __construct(
        private readonly PurchasePolicy $purchasePolicy,
        private readonly StarterKitService $starterKitService, // Keep for now, will refactor later
        private readonly \App\Services\WalletService $walletService
    ) {}

    public function execute(
        User $user,
        string $paymentMethod,
        ?string $paymentReference = null,
        string $tier = 'basic'
    ): array {
        // Get price based on tier
        $priceKwacha = $tier === 'premium' ? 1000 : 500;
        $price = Money::fromKwacha($priceKwacha);
        
        // Calculate wallet balance using WalletService (includes loan transactions)
        $walletBalanceKwacha = (int) $this->walletService->calculateBalance($user);
        $walletBalance = Money::fromKwacha($walletBalanceKwacha);

        // Check if purchase is allowed
        $canPurchase = $this->purchasePolicy->canPurchase(
            $user->has_starter_kit,
            $walletBalance,
            $price,
            $paymentMethod
        );

        if (!$canPurchase) {
            throw new InvalidArgumentException(
                $this->purchasePolicy->getPurchaseDenialReason(
                    $user->has_starter_kit,
                    $walletBalance,
                    $price,
                    $paymentMethod
                )
            );
        }

        // For wallet payments, process immediately
        if ($paymentMethod === 'wallet') {
            return $this->processWalletPayment($user, $price, $tier);
        }

        // For other payments, create pending purchase
        return $this->createPendingPurchase($user, $price, $paymentMethod, $paymentReference, $tier);
    }



    private function processWalletPayment(User $user, Money $price, string $tier): array
    {
        // Use existing service for now (will refactor later)
        // Note: purchaseStarterKit already calls completePurchase for wallet payments
        $purchase = $this->starterKitService->purchaseStarterKit($user, 'wallet', null, $tier);

        return [
            'success' => true,
            'purchase' => $purchase,
            'message' => 'Starter Kit purchased successfully!',
            'redirect' => route('mygrownet.starter-kit.show'),
        ];
    }

    private function createPendingPurchase(
        User $user,
        Money $price,
        string $paymentMethod,
        ?string $paymentReference,
        string $tier
    ): array {
        // Use existing service for now (will refactor later)
        $purchase = $this->starterKitService->purchaseStarterKit(
            $user,
            $paymentMethod,
            $paymentReference,
            $tier
        );

        return [
            'success' => true,
            'purchase' => $purchase,
            'message' => 'Purchase initiated. Please submit your payment details.',
            'redirect' => route('mygrownet.payments.create', [
                'type' => 'product',
                'amount' => $price->toKwacha(),
                'description' => 'Starter Kit Purchase (' . ucfirst($tier) . ' Tier)',
            ]),
        ];
    }
}
