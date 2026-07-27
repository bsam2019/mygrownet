<?php

namespace App\Domain\GrowFinance\Events;

use App\Domain\Core\Events\PlatformEvent;

class AccountBalanceChanged extends PlatformEvent
{
    public const NAME = 'growfinance.account.balance.changed.v1';

    public function __construct(
        public readonly int $accountId,
        public readonly int $companyId,
        public readonly float $previousBalance,
        public readonly float $newBalance,
        public readonly float $changeAmount,
        public readonly string $currency,
    ) {
        parent::__construct(
            entityId: (string) $accountId,
            eventName: self::NAME,
        );
    }

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
