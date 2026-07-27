<?php

namespace App\Domain\BMS\Core\Events;

use App\Domain\Core\Events\PlatformEvent;

class ExpenseRecorded extends PlatformEvent
{
    public const NAME = 'bms.expense.recorded.v1';

    public function __construct(
        public readonly int $expenseId,
        public readonly int $companyId,
        public readonly float $amount,
        public readonly string $category,
        public readonly ?string $description,
    ) {
        parent::__construct(
            entityId: (string) $expenseId,
            eventName: self::NAME,
        );
    }

    public function toPayload(): array
    {
        return [
            'expense_id' => $this->expenseId,
            'company_id' => $this->companyId,
            'amount' => $this->amount,
            'category' => $this->category,
            'description' => $this->description,
        ];
    }
}
