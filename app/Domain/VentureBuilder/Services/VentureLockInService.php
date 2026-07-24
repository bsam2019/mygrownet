<?php

namespace App\Domain\VentureBuilder\Services;

use App\Domain\VentureBuilder\Entities\Investment;
use Carbon\Carbon;

class VentureLockInService
{
    const LOCK_IN_MONTHS = 12;

    public function isLockInPeriodActive(Investment $investment): bool
    {
        $lockInEnd = $investment->paymentConfirmedAt
            ? Carbon::parse($investment->paymentConfirmedAt->format('Y-m-d H:i:s'))->addMonths(self::LOCK_IN_MONTHS)
            : Carbon::parse($investment->createdAt?->format('Y-m-d H:i:s') ?? now())->addMonths(self::LOCK_IN_MONTHS);

        return now()->lessThan($lockInEnd);
    }

    public function getLockInEndDate(Investment $investment): Carbon
    {
        return $investment->paymentConfirmedAt
            ? Carbon::parse($investment->paymentConfirmedAt->format('Y-m-d H:i:s'))->addMonths(self::LOCK_IN_MONTHS)
            : Carbon::parse($investment->createdAt?->format('Y-m-d H:i:s') ?? now())->addMonths(self::LOCK_IN_MONTHS);
    }

    public function getRemainingLockInDays(Investment $investment): int
    {
        if (!$this->isLockInPeriodActive($investment)) {
            return 0;
        }

        return (int) now()->diffInDays($this->getLockInEndDate($investment), false);
    }

    public function assertNotLocked(Investment $investment): void
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
