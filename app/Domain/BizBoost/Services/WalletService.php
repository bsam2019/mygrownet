<?php

namespace App\Domain\BizBoost\Services;

use App\Domain\BizBoost\Contracts\WalletServiceInterface;
use App\Infrastructure\Persistence\Eloquent\BizBoostClientWalletModel;
use App\Infrastructure\Persistence\Eloquent\BizBoostWalletTransactionModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WalletService implements WalletServiceInterface
{
    public function getOrCreateWallet(int $userId, string $currency = 'ZMW'): BizBoostClientWalletModel
    {
        return BizBoostClientWalletModel::firstOrCreate(
            ['user_id' => $userId],
            ['currency' => $currency, 'balance' => 0, 'locked_balance' => 0]
        );
    }

    public function getBalance(int $userId): float
    {
        $wallet = $this->getOrCreateWallet($userId);
        return (float) $wallet->balance;
    }

    public function getAvailableBalance(int $userId): float
    {
        $wallet = $this->getOrCreateWallet($userId);
        return (float) ($wallet->balance - $wallet->locked_balance);
    }

    public function deposit(int $userId, float $amount, string $reference, string $description = '', array $meta = []): BizBoostClientWalletModel
    {
        return DB::transaction(function () use ($userId, $amount, $reference, $description, $meta) {
            $wallet = $this->getOrCreateWallet($userId);
            $balanceBefore = (float) $wallet->balance;
            $wallet->increment('balance', $amount);
            $wallet->refresh();

            BizBoostWalletTransactionModel::create([
                'wallet_id' => $wallet->id,
                'type' => 'deposit',
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => (float) $wallet->balance,
                'currency' => $wallet->currency,
                'reference' => $reference,
                'description' => $description ?: "Deposit of {$amount} {$wallet->currency}",
                'payable_type' => get_class($wallet),
                'payable_id' => $wallet->id,
                'status' => 'completed',
                'meta' => $meta,
            ]);

            return $wallet;
        });
    }

    public function withdraw(int $userId, float $amount, string $reference, string $description = '', array $meta = []): BizBoostClientWalletModel
    {
        return DB::transaction(function () use ($userId, $amount, $reference, $description, $meta) {
            $wallet = $this->getOrCreateWallet($userId);
            $available = (float) ($wallet->balance - $wallet->locked_balance);

            if ($available < $amount) {
                throw new \RuntimeException("Insufficient funds. Available: {$available}, Required: {$amount}");
            }

            $balanceBefore = (float) $wallet->balance;
            $wallet->decrement('balance', $amount);
            $wallet->refresh();

            BizBoostWalletTransactionModel::create([
                'wallet_id' => $wallet->id,
                'type' => 'withdrawal',
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => (float) $wallet->balance,
                'currency' => $wallet->currency,
                'reference' => $reference,
                'description' => $description ?: "Withdrawal of {$amount} {$wallet->currency}",
                'payable_type' => get_class($wallet),
                'payable_id' => $wallet->id,
                'status' => 'completed',
                'meta' => $meta,
            ]);

            return $wallet;
        });
    }

    public function lockFunds(int $userId, float $amount, string $reference): bool
    {
        return DB::transaction(function () use ($userId, $amount, $reference) {
            $wallet = $this->getOrCreateWallet($userId);
            $available = (float) ($wallet->balance - $wallet->locked_balance);

            if ($available < $amount) {
                return false;
            }

            $wallet->increment('locked_balance', $amount);
            return true;
        });
    }

    public function releaseLockedFunds(int $userId, float $amount, string $reference): bool
    {
        return DB::transaction(function () use ($userId, $amount) {
            $wallet = $this->getOrCreateWallet($userId);
            $locked = (float) $wallet->locked_balance;

            if ($locked < $amount) {
                $amount = $locked;
            }

            $wallet->decrement('locked_balance', $amount);
            return true;
        });
    }

    public function hasSufficientFunds(int $userId, float $amount): bool
    {
        return $this->getAvailableBalance($userId) >= $amount;
    }

    public function getTransactions(int $userId, int $limit = 20)
    {
        $wallet = $this->getOrCreateWallet($userId);
        return BizBoostWalletTransactionModel::where('wallet_id', $wallet->id)
            ->orderBy('created_at', 'desc')
            ->paginate($limit);
    }
}
