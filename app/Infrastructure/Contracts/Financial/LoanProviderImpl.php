<?php

namespace App\Infrastructure\Contracts\Financial;

use App\Domain\Financial\Contracts\LoanProvider;
use App\Domain\Financial\Services\LoanService;
use App\Models\User;

class LoanProviderImpl implements LoanProvider
{
    public function __construct(
        private readonly LoanService $loanService
    ) {}

    public function capability(): string
    {
        return 'financial.loan';
    }

    public function getLoanSummary(User $member): array
    {
        return $this->loanService->getLoanSummary($member);
    }

    public function hasOutstandingLoan(User $member): bool
    {
        return $this->loanService->hasOutstandingLoan($member);
    }

    public function canWithdraw(User $member): bool
    {
        return $this->loanService->canWithdraw($member);
    }
}
