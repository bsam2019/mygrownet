<?php

namespace App\Http\Controllers\Investment;

use App\Http\Controllers\Controller;
use App\Models\Investment;
use App\Models\ProfitShare;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProfitShareController extends Controller
{
    public function distribute(Investment $investment)
    {
        try {
            DB::beginTransaction();

            $returnRate = $this->calculateReturnRate($investment->amount);
            $profitAmount = $investment->amount * ($returnRate / 100);

            // Create profit share record
            $profitShare = ProfitShare::create([
                'user_id' => $investment->user_id,
                'investment_id' => $investment->id,
                'amount' => $profitAmount,
                'rate' => $returnRate,
                'distribution_date' => Carbon::now()
            ]);

            // Create transaction record
            Transaction::create([
                'user_id' => $investment->user_id,
                'investment_id' => $investment->id,
                'amount' => $profitAmount,
                'transaction_type' => 'return',
                'status' => 'completed',
                'reference_number' => 'RTN-' . strtoupper(uniqid()),
                'description' => 'Monthly return for investment #' . $investment->id
            ]);

            DB::commit();
            return back()->with('success', 'Profit distributed successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to distribute profit');
        }
    }

    public function distributeAll()
    {
        $activeInvestments = Investment::where('status', 'active')->get();
        $successCount = 0;
        $failCount = 0;

        foreach ($activeInvestments as $investment) {
            try {
                DB::beginTransaction();

                $returnRate = $this->calculateReturnRate($investment->amount);
                $profitAmount = $investment->amount * ($returnRate / 100);

                ProfitShare::create([
                    'user_id' => $investment->user_id,
                    'investment_id' => $investment->id,
                    'amount' => $profitAmount,
                    'rate' => $returnRate,
                    'distribution_date' => Carbon::now()
                ]);

                Transaction::create([
                    'user_id' => $investment->user_id,
                    'investment_id' => $investment->id,
                    'amount' => $profitAmount,
                    'transaction_type' => 'return',
                    'status' => 'completed',
                    'reference_number' => 'RTN-' . strtoupper(uniqid()),
                    'description' => 'Monthly return for investment #' . $investment->id
                ]);

                DB::commit();
                $successCount++;

            } catch (\Exception $e) {
                DB::rollBack();
                $failCount++;
            }
        }

        return back()->with('success', "Processed {$successCount} investments successfully. Failed: {$failCount}");
    }

    private function calculateReturnRate($amount)
    {
        // Return rates based on investment tiers (ZMW)
        return match(true) {
            $amount >= 50000 => 45, // Elite tier: 45%
            $amount >= 25000 => 35, // Leader tier: 35%
            $amount >= 10000 => 25, // Builder tier: 25%
            $amount >= 5000 => 20,  // Starter tier: 20%
            default => 15           // Basic tier: 15%
        };
    }
}
