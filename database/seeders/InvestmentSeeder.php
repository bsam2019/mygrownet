<?php

namespace Database\Seeders;

use App\Models\Investment;
use App\Models\User;
use App\Models\Category;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class InvestmentSeeder extends Seeder
{
    public function run(): void
    {
        // Get all users except admin (by email)
        $users = User::whereNotIn('email', ['admin@vbif.com'])->get();
        $categories = Category::all();
        $startDate = now()->subMonths(12);

        foreach ($users as $user) {
            // Each user gets 1-3 investments
            $investmentCount = rand(1, 3);

            for ($i = 0; $i < $investmentCount; $i++) {
                // Random date between user creation and now
                $investmentDate = Carbon::parse($user->created_at)
                    ->addDays(rand(1, Carbon::parse($user->created_at)->diffInDays(now())));

                $amount = fake()->randomFloat(2, 1000, 100000);

                Investment::create([
                    'user_id' => $user->id,
                    'category_id' => $categories->random()->id,
                    'amount' => $amount,
                    'platform_fee' => $amount * 0.02,
                    'tier' => $this->getTier($amount),
                    'expected_return' => $amount * 0.15,
                    'roi' => fake()->randomFloat(2, 5, 15),
                    'total_earned' => $amount * (fake()->randomFloat(2, 0, 0.2)),
                    'status' => fake()->randomElement(['pending', 'active', 'withdrawn', 'terminated']),
                    'investment_date' => $investmentDate,
                    'interest_earned' => $amount * (fake()->randomFloat(2, 0, 0.1)),
                    'lock_in_period_end' => $investmentDate->copy()->addMonths(6),
                    'last_payout_date' => $investmentDate->copy()->addDays(rand(1, 30)),
                    'maturity_date' => $investmentDate->copy()->addYear(),
                    'created_at' => $investmentDate,
                    'updated_at' => $investmentDate,
                ]);
            }
        }
    }

    private function getTier($amount): string
    {
        return match(true) {
            $amount >= 50000 => 'Elite',
            $amount >= 25000 => 'Premium',
            $amount >= 10000 => 'Advanced',
            default => 'Basic',
        };
    }
}
