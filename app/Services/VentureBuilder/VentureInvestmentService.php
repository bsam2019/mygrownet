<?php

namespace App\Services\VentureBuilder;

use App\Infrastructure\Persistence\Eloquent\VentureBuilder\VentureModel;
use App\Infrastructure\Persistence\Eloquent\VentureBuilder\VentureInvestmentModel;
use App\Infrastructure\Persistence\Eloquent\VentureBuilder\VentureShareholderModel;
use App\Models\AuditLog;
use App\Models\User;
use App\Events\VentureBuilder\VentureInvestmentConfirmed;
use App\Services\ReceiptService;
use App\Services\VentureBuilder\VentureLockInService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;

class VentureInvestmentService
{
    public function __construct(
        private readonly VentureService $ventureService,
        private readonly VentureLockInService $lockInService,
        private readonly ReceiptService $receiptService,
    ) {}

    public function processWalletInvestment(
        User $user,
        VentureModel $venture,
        float $amount
    ): VentureInvestmentModel {
        if ($user->wallet_balance < $amount) {
            throw new \InvalidArgumentException('Insufficient wallet balance.');
        }

        if ($venture->status !== 'funding') {
            throw new \InvalidArgumentException('This venture is not currently accepting investments.');
        }

        $remainingFunding = $venture->funding_target - $venture->total_raised;
        if ($amount > $remainingFunding) {
            throw new \InvalidArgumentException('Investment amount exceeds remaining funding needed.');
        }

        if ($venture->maximum_investment && $amount > $venture->maximum_investment) {
            throw new \InvalidArgumentException('Investment amount exceeds maximum allowed.');
        }

        if ($amount < $venture->minimum_investment) {
            throw new \InvalidArgumentException('Investment amount is below the minimum.');
        }

        DB::beginTransaction();
        try {
            $shares = $this->ventureService->calculateShares($amount, $venture);

            $investment = VentureInvestmentModel::create([
                'user_id' => $user->id,
                'venture_id' => $venture->id,
                'amount' => $amount,
                'shares_allocated' => $shares,
                'payment_method' => 'wallet',
                'payment_reference' => 'WALLET_' . strtoupper(Str::random(10)),
                'status' => 'confirmed',
                'payment_confirmed_at' => now(),
            ]);

            $user->decrement('wallet_balance', $amount);
            $venture->increment('total_raised', $amount);
            $venture->increment('investor_count');

            $this->receiptService->createReceipt([
                'user_id' => $user->id,
                'type' => 'venture_investment',
                'amount' => $amount,
                'description' => "Investment in {$venture->title}",
                'reference_id' => $investment->id,
                'payment_method' => 'wallet',
                'payment_reference' => $investment->payment_reference,
            ]);

            Event::dispatch(new VentureInvestmentConfirmed($investment));

            AuditLog::logEvent(
                'venture_investment_confirmed',
                $investment,
                $user->id,
                null,
                $investment->toArray(),
                $amount,
                $investment->payment_reference,
                [
                    'venture_title' => $venture->title,
                    'venture_id' => $venture->id,
                    'shares' => $shares,
                    'payment_method' => 'wallet',
                ]
            );

            $this->ventureService->checkFundingComplete($venture);

            DB::commit();

            return $investment;
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function initiateMobileMoneyInvestment(
        User $user,
        VentureModel $venture,
        float $amount
    ): VentureInvestmentModel {
        if ($venture->status !== 'funding') {
            throw new \InvalidArgumentException('This venture is not currently accepting investments.');
        }

        $remainingFunding = $venture->funding_target - $venture->total_raised;
        if ($amount > $remainingFunding) {
            throw new \InvalidArgumentException('Investment amount exceeds remaining funding needed.');
        }

        if ($venture->maximum_investment && $amount > $venture->maximum_investment) {
            throw new \InvalidArgumentException('Investment amount exceeds maximum allowed.');
        }

        if ($amount < $venture->minimum_investment) {
            throw new \InvalidArgumentException('Investment amount is below the minimum.');
        }

        $shares = $this->ventureService->calculateShares($amount, $venture);

        $investment = VentureInvestmentModel::create([
            'user_id' => $user->id,
            'venture_id' => $venture->id,
            'amount' => $amount,
            'shares_allocated' => $shares,
            'payment_method' => 'mobile_money',
            'payment_reference' => 'MM_' . strtoupper(Str::random(10)),
            'status' => 'pending',
        ]);

        AuditLog::logEvent(
            'venture_investment_pending',
            $investment,
            $user->id,
            null,
            $investment->toArray(),
            $amount,
            $investment->payment_reference,
            [
                'venture_title' => $venture->title,
                'venture_id' => $venture->id,
                'shares' => $shares,
                'payment_method' => 'mobile_money',
            ]
        );

        return $investment;
    }

    public function confirmInvestment(VentureInvestmentModel $investment): VentureInvestmentModel
    {
        if (!$investment->isPending()) {
            throw new \InvalidArgumentException('Only pending investments can be confirmed.');
        }

        DB::beginTransaction();
        try {
            $investment->update([
                'status' => 'confirmed',
                'payment_confirmed_at' => now(),
                'processed_by' => Auth::id(),
                'processed_at' => now(),
            ]);

            $venture = $investment->venture;
            $venture->increment('total_raised', $investment->amount);
            $venture->increment('investor_count');

            $this->receiptService->createReceipt([
                'user_id' => $investment->user_id,
                'type' => 'venture_investment',
                'amount' => $investment->amount,
                'description' => "Investment in {$venture->title}",
                'reference_id' => $investment->id,
                'payment_method' => $investment->payment_method,
                'payment_reference' => $investment->payment_reference,
            ]);

            Event::dispatch(new VentureInvestmentConfirmed($investment));

            AuditLog::logEvent(
                'venture_investment_confirmed',
                $investment,
                Auth::id(),
                ['status' => 'pending'],
                $investment->toArray(),
                $investment->amount,
                $investment->payment_reference,
                ['admin_confirmed' => true]
            );

            $this->ventureService->checkFundingComplete($venture);

            DB::commit();

            return $investment->fresh();
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function refundInvestment(VentureInvestmentModel $investment): VentureInvestmentModel
    {
        if (!in_array($investment->status, ['pending', 'confirmed'])) {
            throw new \InvalidArgumentException('Only pending or confirmed investments can be refunded.');
        }

        if ($investment->status === 'confirmed') {
            $this->lockInService->assertNotLocked($investment);
        }

        DB::beginTransaction();
        try {
            $oldStatus = $investment->status;

            if ($oldStatus === 'confirmed') {
                $investment->user->increment('wallet_balance', $investment->amount);
                $venture = $investment->venture;
                $venture->decrement('total_raised', $investment->amount);
                $venture->decrement('investor_count');

                if ($venture->status === 'funded') {
                    $venture->update(['status' => 'funding']);
                }
            }

            $investment->update([
                'status' => 'refunded',
                'processed_by' => Auth::id(),
                'processed_at' => now(),
            ]);

            AuditLog::logEvent(
                'venture_investment_refunded',
                $investment,
                Auth::id(),
                ['status' => $oldStatus],
                $investment->toArray(),
                $investment->amount,
                $investment->payment_reference,
                ['admin_refunded' => true]
            );

            DB::commit();

            return $investment->fresh();
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function registerShareholder(
        VentureInvestmentModel $investment,
        ?string $certificateNumber = null
    ): VentureShareholderModel {
        if (!$investment->isConfirmed()) {
            throw new \InvalidArgumentException('Only confirmed investments can be registered as shareholders.');
        }

        $venture = $investment->venture;
        $totalInvested = VentureInvestmentModel::where('venture_id', $venture->id)
            ->where('user_id', $investment->user_id)
            ->confirmed()
            ->sum('amount');

        $totalRaised = $venture->total_raised > 0 ? $venture->total_raised : 1;

        return VentureShareholderModel::create([
            'venture_id' => $venture->id,
            'user_id' => $investment->user_id,
            'investment_id' => $investment->id,
            'total_investment' => $totalInvested,
            'shares_owned' => $investment->shares_allocated,
            'equity_percentage' => min(100, ($totalInvested / $totalRaised) * 100),
            'certificate_number' => $certificateNumber ?? 'SH-' . strtoupper(Str::random(8)),
            'registration_date' => now(),
            'status' => 'active',
        ]);
    }
}
