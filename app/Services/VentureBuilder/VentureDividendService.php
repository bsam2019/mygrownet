<?php

namespace App\Services\VentureBuilder;

use App\Events\VentureBuilder\VentureDividendPaid;
use App\Infrastructure\Persistence\Eloquent\VentureBuilder\VentureModel;
use App\Infrastructure\Persistence\Eloquent\VentureBuilder\VentureDividendModel;
use App\Infrastructure\Persistence\Eloquent\VentureBuilder\VentureShareholderModel;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

class VentureDividendService
{
    public function declareDividend(
        VentureModel $venture,
        string $period,
        float $totalAmount,
        ?string $notes = null
    ): array {
        if ($venture->status !== 'active') {
            throw new \InvalidArgumentException('Only active ventures can declare dividends.');
        }

        $shareholders = VentureShareholderModel::where('venture_id', $venture->id)
            ->active()
            ->get();

        if ($shareholders->isEmpty()) {
            throw new \InvalidArgumentException('No active shareholders to distribute dividends to.');
        }

        $dividends = [];
        $totalEquity = $shareholders->sum('equity_percentage');

        DB::beginTransaction();
        try {
            foreach ($shareholders as $shareholder) {
                $shareAmount = $totalEquity > 0
                    ? ($shareholder->equity_percentage / $totalEquity) * $totalAmount
                    : $totalAmount / $shareholders->count();

                $dividend = VentureDividendModel::create([
                    'venture_id' => $venture->id,
                    'shareholder_id' => $shareholder->id,
                    'dividend_period' => $period,
                    'declaration_date' => now(),
                    'amount' => round($shareAmount, 2),
                    'equity_percentage_at_payment' => $shareholder->equity_percentage,
                    'status' => 'declared',
                    'notes' => $notes,
                    'processed_by' => Auth::id(),
                ]);

                $dividends[] = $dividend;
            }

            AuditLog::logEvent(
                'venture_dividend_declared',
                $venture,
                Auth::id(),
                null,
                ['period' => $period, 'total_amount' => $totalAmount, 'shareholder_count' => $shareholders->count()],
                $totalAmount,
                null,
                [
                    'venture_title' => $venture->title,
                    'period' => $period,
                    'shareholder_count' => $shareholders->count(),
                ]
            );

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }

        return $dividends;
    }

    public function processDividend(VentureDividendModel $dividend): VentureDividendModel
    {
        if ($dividend->status !== 'declared') {
            throw new \InvalidArgumentException('Only declared dividends can be processed.');
        }

        DB::beginTransaction();
        try {
            $shareholder = $dividend->shareholder;
            $user = $shareholder->user;

            $user->increment('wallet_balance', $dividend->amount);

            $dividend->update([
                'status' => 'paid',
                'payment_date' => now(),
                'paid_at' => now(),
                'payment_method' => 'wallet',
                'payment_reference' => 'DIV-' . strtoupper(\Illuminate\Support\Str::random(10)),
                'processed_by' => Auth::id(),
            ]);

            $shareholder->increment('total_dividends_received', $dividend->amount);
            $shareholder->update(['last_dividend_date' => now()]);

            Event::dispatch(new VentureDividendPaid($dividend));

            AuditLog::logEvent(
                'venture_dividend_paid',
                $dividend,
                $user->id,
                ['status' => 'declared'],
                $dividend->toArray(),
                $dividend->amount,
                $dividend->payment_reference,
                [
                    'venture_id' => $dividend->venture_id,
                    'shareholder_id' => $shareholder->id,
                    'period' => $dividend->dividend_period,
                ]
            );

            DB::commit();

            return $dividend->fresh();
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }
}
