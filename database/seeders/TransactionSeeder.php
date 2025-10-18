<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Investment;
use App\Models\Transaction;
use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $investments = Investment::all();

        foreach ($investments as $investment) {
            // Create deposit transaction for each investment
            Transaction::create([
                'user_id' => $investment->user_id,
                'investment_id' => $investment->id,
                'amount' => $investment->amount,
                'transaction_type' => 'deposit',
                'status' => 'completed',
                'payment_method' => fake()->randomElement(['bank_transfer', 'card', 'crypto']),
                'reference_number' => 'TXN-' . fake()->unique()->regexify('[A-Z0-9]{10}'),
                'description' => "Investment deposit",
                'created_at' => $investment->created_at,
            ]);

            // Create some return transactions (only if investment is not in the future)
            if ($investment->created_at <= now()) {
                $returns = rand(1, 3);
                for ($i = 0; $i < $returns; $i++) {
                    Transaction::create([
                        'user_id' => $investment->user_id,
                        'investment_id' => $investment->id,
                        'amount' => $investment->amount * (rand(5, 15) / 100), // 5-15% returns
                        'transaction_type' => 'return',
                        'status' => 'completed',
                        'reference_number' => 'TXN-' . fake()->unique()->regexify('[A-Z0-9]{10}'),
                        'description' => "Investment return",
                        'created_at' => fake()->dateTimeBetween($investment->created_at, 'now'),
                    ]);
                }
            }
        }

        // Create some withdrawal transactions
        foreach ($users as $user) {
            if (rand(0, 1)) {
                Transaction::create([
                    'user_id' => $user->id,
                    'amount' => fake()->randomFloat(2, 100, 1000),
                    'transaction_type' => 'withdrawal',
                    'status' => fake()->randomElement(['pending', 'completed', 'failed']),
                    'reference_number' => 'TXN-' . fake()->unique()->regexify('[A-Z0-9]{10}'),
                    'description' => "Withdrawal request",
                    'created_at' => fake()->dateTimeBetween('-1 month', 'now'),
                ]);
            }
        }
    }
}
