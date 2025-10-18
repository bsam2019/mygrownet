<?php

namespace App\Services;

use App\Models\User;
use App\Models\Investment;
use App\Models\Transaction;
use Illuminate\Support\Str;

class TransactionService
{
    public function createInvestmentTransaction(User $user, Investment $investment, array $data)
    {
        return Transaction::create([
            'user_id' => $user->id,
            'investment_id' => $investment->id,
            'amount' => $data['amount'],
            'transaction_type' => 'deposit',
            'status' => 'pending',
            'payment_method' => $data['payment_method'],
            'reference_number' => $this->generateReferenceNumber(),
            'description' => "Investment in {$investment->tier->name} tier"
        ]);
    }

    public function createReturnTransaction(Investment $investment, float $amount)
    {
        return Transaction::create([
            'user_id' => $investment->user_id,
            'investment_id' => $investment->id,
            'amount' => $amount,
            'transaction_type' => 'return',
            'status' => 'completed',
            'reference_number' => $this->generateReferenceNumber(),
            'description' => "Return on investment {$investment->id}"
        ]);
    }

    public function createWithdrawalTransaction(User $user, float $amount)
    {
        return Transaction::create([
            'user_id' => $user->id,
            'investment_id' => null,
            'amount' => $amount,
            'transaction_type' => 'withdrawal',
            'status' => 'pending',
            'reference_number' => $this->generateReferenceNumber(),
            'description' => "Withdrawal request"
        ]);
    }

    public function updateTransactionStatus(Transaction $transaction, string $status)
    {
        return $transaction->update(['status' => $status]);
    }

    private function generateReferenceNumber(): string
    {
        return 'TXN-' . Str::random(10);
    }

    public function getUserTransactions(User $user, array $filters = [])
    {
        $query = $user->transactions()->with(['investment']);

        if (isset($filters['type'])) {
            $query->where('transaction_type', $filters['type']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->latest()->paginate(10);
    }
}
