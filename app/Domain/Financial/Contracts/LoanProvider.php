<?php

namespace App\Domain\Financial\Contracts;

use App\Domain\Core\Contracts\ProviderContract;
use App\Models\User;

interface LoanProvider extends ProviderContract
{
    public function getLoanSummary(User $member): array;

    public function hasOutstandingLoan(User $member): bool;

    public function canWithdraw(User $member): bool;
}
