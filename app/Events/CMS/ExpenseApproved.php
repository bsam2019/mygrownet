<?php

namespace App\Events\CMS;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ExpenseApproved
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public int $expenseId,
        public int $companyId,
        public float $amount,
        public string $category,
        public ?string $description = null,
        public ?string $referenceNumber = null
    ) {}
}
