<?php

namespace App\Domain\VentureBuilder\Services;

use App\Domain\VentureBuilder\Repositories\InvestmentRepositoryInterface;
use App\Domain\VentureBuilder\Repositories\VentureRepositoryInterface;
use App\Domain\VentureBuilder\Repositories\ShareholderRepositoryInterface;
use App\Domain\VentureBuilder\Entities\Investment;
use App\Domain\VentureBuilder\Entities\Shareholder;
use App\Domain\VentureBuilder\ValueObjects\InvestmentStatus;
use App\Events\VentureBuilder\VentureInvestmentConfirmed;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;

class VentureInvestmentService
{
    public function __construct(
        private readonly InvestmentRepositoryInterface $investmentRepository,
        private readonly VentureRepositoryInterface $ventureRepository,
        private readonly ShareholderRepositoryInterface $shareholderRepository,
        private readonly VentureService $ventureService,
        private readonly VentureLockInService $lockInService,
    ) {}

    public function processWalletInvestment(
        int $userId,
        int $ventureId,
        float $amount
    ): array {
        $walletBalance = (float) DB::table('users')->where('id', $userId)->value('wallet_balance');
        if ($walletBalance < $amount) {
            throw new \InvalidArgumentException('Insufficient wallet balance.');
        }

        $venture = $this->ventureRepository->findById($ventureId);
        if (!$venture || $venture->status->value() !== 'funding') {
            throw new \InvalidArgumentException('This venture is not currently accepting investments.');
        }

        $remainingFunding = ($venture->fundingTarget ?? 0) - ($venture->totalRaised ?? 0);
        if ($amount > $remainingFunding) {
            throw new \InvalidArgumentException('Investment amount exceeds remaining funding needed.');
        }

        if ($venture->maximumInvestment && $amount > $venture->maximumInvestment) {
            throw new \InvalidArgumentException('Investment amount exceeds maximum allowed.');
        }

        if ($amount < ($venture->minimumInvestment ?? 0)) {
            throw new \InvalidArgumentException('Investment amount is below the minimum.');
        }

        return DB::transaction(function () use ($userId, $venture, $amount, $ventureId) {
            $shares = $this->ventureService->calculateShares($amount, $venture->toArray());

            $investment = new Investment(
                ventureId: $ventureId,
                userId: $userId,
                amount: $amount,
                sharesAllocated: $shares,
                status: InvestmentStatus::confirmed(),
                paymentMethod: 'wallet',
                paymentReference: 'WALLET_' . strtoupper(Str::random(10)),
                paymentConfirmedAt: new \DateTimeImmutable(),
                createdAt: new \DateTimeImmutable(),
            );

            $saved = $this->investmentRepository->save($investment);

            DB::table('users')->where('id', $userId)->decrement('wallet_balance', $amount);
            $this->ventureRepository->incrementTotalRaised($ventureId, $amount);
            $this->ventureRepository->incrementInvestorCount($ventureId);

            $receiptData = [
                'user_id' => $userId,
                'type' => 'venture_investment',
                'amount' => $amount,
                'description' => "Investment in {$venture->title}",
                'reference_id' => $saved->id,
                'payment_method' => 'wallet',
                'payment_reference' => $saved->paymentReference,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            DB::table('receipts')->insert($receiptData);

            Event::dispatch(new VentureInvestmentConfirmed(
                investmentId: $saved->id ?? 0,
                ventureId: $ventureId,
                userId: $userId,
                amount: $amount,
                paymentReference: $saved->paymentReference ?? '',
                ventureTitle: $venture->title,
            ));

            AuditLog::logEvent(
                'venture_investment_confirmed',
                "Investment#{$saved->id}",
                $userId,
                null,
                $saved->toArray(),
                $amount,
                $saved->paymentReference,
                [
                    'venture_title' => $venture->title,
                    'venture_id' => $ventureId,
                    'shares' => $shares,
                    'payment_method' => 'wallet',
                ]
            );

            $this->ventureService->checkFundingComplete($ventureId);

            return $saved->toArray();
        });
    }

    public function initiateMobileMoneyInvestment(
        int $userId,
        int $ventureId,
        float $amount
    ): array {
        $venture = $this->ventureRepository->findById($ventureId);
        if (!$venture || $venture->status->value() !== 'funding') {
            throw new \InvalidArgumentException('This venture is not currently accepting investments.');
        }

        $remainingFunding = ($venture->fundingTarget ?? 0) - ($venture->totalRaised ?? 0);
        if ($amount > $remainingFunding) {
            throw new \InvalidArgumentException('Investment amount exceeds remaining funding needed.');
        }

        if ($venture->maximumInvestment && $amount > $venture->maximumInvestment) {
            throw new \InvalidArgumentException('Investment amount exceeds maximum allowed.');
        }

        if ($amount < ($venture->minimumInvestment ?? 0)) {
            throw new \InvalidArgumentException('Investment amount is below the minimum.');
        }

        $shares = $this->ventureService->calculateShares($amount, $venture->toArray());

        $investment = new Investment(
            ventureId: $ventureId,
            userId: $userId,
            amount: $amount,
            sharesAllocated: $shares,
            status: InvestmentStatus::pending(),
            paymentMethod: 'mobile_money',
            paymentReference: 'MM_' . strtoupper(Str::random(10)),
        );

        $saved = $this->investmentRepository->save($investment);

        AuditLog::logEvent(
            'venture_investment_pending',
            "Investment#{$saved->id}",
            $userId,
            null,
            $saved->toArray(),
            $amount,
            $saved->paymentReference,
            [
                'venture_title' => $venture->title,
                'venture_id' => $ventureId,
                'shares' => $shares,
                'payment_method' => 'mobile_money',
            ]
        );

        return $saved->toArray();
    }

    public function confirmInvestment(int $investmentId): array
    {
        $investment = $this->investmentRepository->findById($investmentId);
        if (!$investment || !$investment->isPending()) {
            throw new \InvalidArgumentException('Only pending investments can be confirmed.');
        }

        return DB::transaction(function () use ($investment, $investmentId) {
            $extra = [
                'payment_confirmed_at' => now(),
                'processed_by' => Auth::id(),
                'processed_at' => now(),
            ];
            $this->investmentRepository->updateStatus($investmentId, 'confirmed', $extra);

            $this->ventureRepository->incrementTotalRaised($investment->ventureId, $investment->amount);
            $this->ventureRepository->incrementInvestorCount($investment->ventureId);

            $venture = $this->ventureRepository->findById($investment->ventureId);
            $ventureTitle = $venture?->title ?? 'Unknown';

            $receiptData = [
                'user_id' => $investment->userId,
                'type' => 'venture_investment',
                'amount' => $investment->amount,
                'description' => "Investment in {$ventureTitle}",
                'reference_id' => $investmentId,
                'payment_method' => $investment->paymentMethod,
                'payment_reference' => $investment->paymentReference,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            DB::table('receipts')->insert($receiptData);

            Event::dispatch(new VentureInvestmentConfirmed(
                investmentId: $investmentId,
                ventureId: $investment->ventureId,
                userId: $investment->userId,
                amount: $investment->amount,
                paymentReference: $investment->paymentReference ?? '',
                ventureTitle: $ventureTitle,
            ));

            AuditLog::logEvent(
                'venture_investment_confirmed',
                "Investment#$investmentId",
                Auth::id(),
                ['status' => 'pending'],
                [],
                $investment->amount,
                $investment->paymentReference,
                ['admin_confirmed' => true]
            );

            $this->ventureService->checkFundingComplete($investment->ventureId);

            $updated = $this->investmentRepository->findById($investmentId);
            return $updated ? $updated->toArray() : [];
        });
    }

    public function refundInvestment(int $investmentId): array
    {
        $investment = $this->investmentRepository->findById($investmentId);
        if (!$investment || !in_array($investment->status->value(), ['pending', 'confirmed'])) {
            throw new \InvalidArgumentException('Only pending or confirmed investments can be refunded.');
        }

        if ($investment->status->value() === 'confirmed') {
            $this->lockInService->assertNotLocked($investment);
        }

        return DB::transaction(function () use ($investment, $investmentId) {
            $oldStatus = $investment->status->value();

            if ($oldStatus === 'confirmed') {
                DB::table('users')->where('id', $investment->userId)->increment('wallet_balance', $investment->amount);
                $this->ventureRepository->decrementTotalRaised($investment->ventureId, $investment->amount);
                $this->ventureRepository->decrementInvestorCount($investment->ventureId);

                $venture = $this->ventureRepository->findById($investment->ventureId);
                if ($venture && $venture->status->value() === 'funded') {
                    $this->ventureRepository->updateStatus($investment->ventureId, 'funding');
                }
            }

            $this->investmentRepository->updateStatus($investmentId, 'refunded');

            AuditLog::logEvent(
                'venture_investment_refunded',
                "Investment#$investmentId",
                Auth::id(),
                ['status' => $oldStatus],
                [],
                $investment->amount,
                $investment->paymentReference,
                ['admin_refunded' => true]
            );

            $updated = $this->investmentRepository->findById($investmentId);
            return $updated ? $updated->toArray() : [];
        });
    }

    public function registerShareholder(
        int $investmentId,
        ?string $certificateNumber = null
    ): array {
        $investment = $this->investmentRepository->findById($investmentId);
        if (!$investment || !$investment->isConfirmed()) {
            throw new \InvalidArgumentException('Only confirmed investments can be registered as shareholders.');
        }

        $totalInvested = $this->investmentRepository->getTotalInvestedByUser($investment->userId, $investment->ventureId);

        $venture = $this->ventureRepository->findById($investment->ventureId);
        $totalRaised = ($venture?->totalRaised ?? 0) > 0 ? ($venture->totalRaised ?? 1) : 1;

        $shareholder = new Shareholder(
            ventureId: $investment->ventureId,
            userId: $investment->userId,
            investmentId: $investmentId,
            totalInvestment: $totalInvested,
            sharesOwned: $investment->sharesAllocated ?? 0,
            equityPercentage: min(100, ($totalInvested / $totalRaised) * 100),
            certificateNumber: $certificateNumber ?? 'SH-' . strtoupper(Str::random(8)),
            status: \App\Domain\VentureBuilder\ValueObjects\ShareholderStatus::active(),
        );

        $saved = $this->shareholderRepository->save($shareholder);
        return $saved->toArray();
    }
}
