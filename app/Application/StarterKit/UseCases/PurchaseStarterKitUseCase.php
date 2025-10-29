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
        
        // Calculate wallet balance dynamically
        $walletBalanceKwacha = $this->calculateWalletBalance($user);
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
            return $this->processWalletPayment($user, $price);
        }

        // For other payments, create pending purchase
        return $this->createPendingPurchase($user, $price, $paymentMethod, $paymentReference);
    }

    private function calculateWalletBalance(User $user): int
    {
        $commissionEarnings = (float) ($user->referralCommissions()->where('status', 'paid')->sum('amount') ?? 0);
        $profitEarnings = (float) ($user->profitShares()->sum('amount') ?? 0);
        $walletTopups = (float) (\App\Infrastructure\Persistence\Eloquent\Payment\MemberPaymentModel::where('user_id', $user->id)
            ->where('payment_type', 'wallet_topup')
            ->where('status', 'verified')
            ->sum('amount') ?? 0);
        $totalEarnings = $commissionEarnings + $profitEarnings + $walletTopups;
        $totalWithdrawals = (float) ($user->withdrawals()->where('status', 'approved')->sum('amount') ?? 0);
        $workshopExpenses = (float) (\App\Infrastructure\Persistence\Eloquent\Workshop\WorkshopRegistrationModel::where('workshop_registrations.user_id', $user->id)
            ->whereIn('workshop_registrations.status', ['registered', 'attended', 'completed'])
            ->join('workshops', 'workshop_registrations.workshop_id', '=', 'workshops.id')
            ->sum('workshops.price') ?? 0);
        $transactionExpenses = (float) (\Illuminate\Support\Facades\DB::table('transactions')
            ->where('user_id', $user->id)
            ->where('status', 'completed')
            ->where('transaction_type', 'withdrawal')
            ->sum('amount') ?? 0);
        
        return (int) ($totalEarnings - $totalWithdrawals - $workshopExpenses - $transactionExpenses);
    }

    private function processWalletPayment(User $user, Money $price): array
    {
        // Use existing service for now (will refactor later)
        // Note: purchaseStarterKit already calls completePurchase for wallet payments
        $purchase = $this->starterKitService->purchaseStarterKit($user, 'wallet');

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
        $purchase = $this->starterKitService->purchaseStarterKit(
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
