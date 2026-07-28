<?php

namespace App\Domain\GrowFinance\Events;

class AccountBalanceChanged
{
    public const NAME = 'growfinance.account.balance.changed.v1';

    public function __construct(
        public readonly int $accountId,
        public readonly int $companyId,
        public readonly float $previousBalance,
        public readonly float $newBalance,
        public readonly float $changeAmount,
        public readonly string $currency,
    ) {}

    public function toPayload(): array
    {
        return [
            'account_id' => $this->accountId,
            'company_id' => $this->companyId,
            'previous_balance' => $this->previousBalance,
            'new_balance' => $this->newBalance,
            'change_amount' => $this->changeAmount,
            'currency' => $this->currency,
        ];
    }
}
