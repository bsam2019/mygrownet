<?php

namespace App\Domain\VentureBuilder\Services;

use App\Domain\VentureBuilder\Repositories\DividendRepositoryInterface;
use App\Domain\VentureBuilder\Repositories\ShareholderRepositoryInterface;
use App\Domain\VentureBuilder\Repositories\VentureRepositoryInterface;
use App\Events\VentureBuilder\VentureDividendPaid;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

class VentureDividendService
{
    public function __construct(
        private readonly DividendRepositoryInterface $dividendRepository,
        private readonly ShareholderRepositoryInterface $shareholderRepository,
        private readonly VentureRepositoryInterface $ventureRepository,
    ) {}

    public function declareDividend(
        int $ventureId,
        string $period,
        float $totalAmount,
        ?string $notes = null
    ): array {
        $venture = $this->ventureRepository->findById($ventureId);
        if (!$venture || $venture->status->value() !== 'active') {
            throw new \InvalidArgumentException('Only active ventures can declare dividends.');
        }

        $shareholders = $this->shareholderRepository->findActiveByVenture($ventureId);
        if (empty($shareholders)) {
            throw new \InvalidArgumentException('No active shareholders to distribute dividends to.');
        }

        $totalEquity = array_sum(array_map(fn($s) => $s->equityPercentage ?? 0, $shareholders));

        return DB::transaction(function () use ($ventureId, $period, $totalAmount, $notes, $shareholders, $totalEquity, $venture) {
            $dividends = [];
            $shareholderCount = count($shareholders);

            foreach ($shareholders as $shareholder) {
                $shareAmount = $totalEquity > 0
                    ? ($shareholder->equityPercentage / $totalEquity) * $totalAmount
                    : $totalAmount / $shareholderCount;

                $dividend = $this->dividendRepository->create([
                    'venture_id' => $ventureId,
                    'shareholder_id' => $shareholder->id,
                    'dividend_period' => $period,
                    'declaration_date' => now(),
                    'amount' => round($shareAmount, 2),
                    'equity_percentage_at_payment' => $shareholder->equityPercentage,
                    'status' => 'declared',
                    'notes' => $notes,
                    'processed_by' => Auth::id(),
                ]);

                $dividends[] = $dividend->toArray();
            }

            AuditLog::logEvent(
                'venture_dividend_declared',
                "Venture#$ventureId",
                Auth::id(),
                null,
                ['period' => $period, 'total_amount' => $totalAmount, 'shareholder_count' => $shareholderCount],
                $totalAmount,
                null,
                [
                    'venture_title' => $venture->title,
                    'period' => $period,
                    'shareholder_count' => $shareholderCount,
                ]
            );

            return $dividends;
        });
    }

    public function processDividend(int $dividendId): array
    {
        $dividend = $this->dividendRepository->findById($dividendId);
        if (!$dividend || $dividend->status->value() !== 'declared') {
            throw new \InvalidArgumentException('Only declared dividends can be processed.');
        }

        return DB::transaction(function () use ($dividend, $dividendId) {
            $shareholder = $this->shareholderRepository->findById($dividend->shareholderId);
            if (!$shareholder) {
                throw new \InvalidArgumentException('Shareholder not found.');
            }

            $userId = $shareholder->userId;
            DB::table('users')->where('id', $userId)->increment('wallet_balance', $dividend->amount);

            $paymentReference = 'DIV-' . strtoupper(\Illuminate\Support\Str::random(10));
            $this->dividendRepository->updateStatus($dividendId, 'paid', [
                'payment_date' => now(),
                'paid_at' => now(),
                'payment_method' => 'wallet',
                'payment_reference' => $paymentReference,
                'processed_by' => Auth::id(),
            ]);

            AuditLog::logEvent(
                'venture_dividend_paid',
                "Dividend#$dividendId",
                $userId,
                ['status' => 'declared'],
                [],
                $dividend->amount,
                $paymentReference,
                [
                    'venture_id' => $dividend->ventureId,
                    'shareholder_id' => $dividend->shareholderId,
                    'period' => $dividend->period,
                ]
            );

            Event::dispatch(new VentureDividendPaid(
                dividendId: $dividendId,
                ventureId: $dividend->ventureId,
                shareholderId: $dividend->shareholderId,
                userId: $userId,
                amount: $dividend->amount,
                paymentReference: $paymentReference,
                period: $dividend->period,
            ));

            $updated = $this->dividendRepository->findById($dividendId);
            return $updated ? $updated->toArray() : [];
        });
    }
}
