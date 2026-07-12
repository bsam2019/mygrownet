<?php

namespace App\Services\VentureBuilder;

use App\Infrastructure\Persistence\Eloquent\VentureBuilder\VentureInvestmentModel;
use Carbon\Carbon;

class VentureLockInService
{
    const LOCK_IN_MONTHS = 12;

    public function isLockInPeriodActive(VentureInvestmentModel $investment): bool
    {
        $lockInEnd = $investment->payment_confirmed_at
            ? Carbon::parse($investment->payment_confirmed_at)->addMonths(self::LOCK_IN_MONTHS)
            : Carbon::parse($investment->created_at)->addMonths(self::LOCK_IN_MONTHS);

        return now()->lessThan($lockInEnd);
    }

    public function getLockInEndDate(VentureInvestmentModel $investment): Carbon
    {
        return $investment->payment_confirmed_at
            ? Carbon::parse($investment->payment_confirmed_at)->addMonths(self::LOCK_IN_MONTHS)
            : Carbon::parse($investment->created_at)->addMonths(self::LOCK_IN_MONTHS);
    }

    public function getRemainingLockInDays(VentureInvestmentModel $investment): int
    {
        if (!$this->isLockInPeriodActive($investment)) {
            return 0;
        }

        return (int) now()->diffInDays($this->getLockInEndDate($investment), false);
    }

    public function assertNotLocked(VentureInvestmentModel $investment): void
    {
        if ($this->isLockInPeriodActive($investment)) {
            $remaining = $this->getRemainingLockInDays($investment);
            throw new \RuntimeException(
                "This investment is in a lock-in period. {$remaining} day(s) remaining before you can withdraw. " .
                "Lock-in period is " . self::LOCK_IN_MONTHS . " months from the investment date."
            );
        }
    }
}
