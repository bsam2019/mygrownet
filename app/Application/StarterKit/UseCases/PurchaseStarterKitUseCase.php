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
        private readonly StarterKitService $starterKitService // Keep for now, will refactor later
    ) {}

    public function execute(
        User $user,
        string $paymentMethod,
        ?string $paymentReference = null
    ): array {
        // Create value objects
        $price = Money::fromKwacha(self::PRICE_KWACHA);
        $walletBalance = Money::fromKwacha((int) $user->wallet_balance);

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
            return $this->processWalletPayment($user, $price);
        }

        // For other payments, create pending purchase
        return $this->createPendingPurchase($user, $price, $paymentMethod, $paymentReference);
    }

    private function processWalletPayment(User $user, Money $price): array
    {
        // Use existing service for now (will refactor later)
        $purchase = $this->starterKitService->purchaseWithWallet($user);

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
        ?string $paymentReference
    ): array {
        // Use existing service for now (will refactor later)
        $purchase = $this->starterKitService->createPendingPurchase(
            $user,
            $paymentMethod,
            $paymentReference
        );

        return [
            'success' => true,
            'purchase' => $purchase,
            'message' => 'Purchase initiated. Please submit your payment details.',
            'redirect' => route('mygrownet.payments.create', [
                'type' => 'product',
                'amount' => $price->toKwacha(),
                'description' => 'Starter Kit Purchase',
            ]),
        ];
    }
}
