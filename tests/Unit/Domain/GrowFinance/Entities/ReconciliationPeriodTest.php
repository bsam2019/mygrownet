<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\GrowFinance\Entities;

use App\Domain\GrowFinance\Entities\ReconciliationPeriod;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ReconciliationPeriodTest extends TestCase
{
    #[Test]
    public function constructor_sets_properties()
    {
        $period = new ReconciliationPeriod(
            id: 1, businessId: 5, bankAccountId: 10,
            startDate: null, endDate: null, openingBalance: null,
            closingBalance: null, bookBalance: null, difference: null,
            status: 'open', createdBy: 1, completedBy: null,
            completedAt: null, notes: null, createdAt: null, updatedAt: null,
        );

        $this->assertSame(1, $period->id);
        $this->assertSame(5, $period->businessId);
        $this->assertSame('open', $period->status);
    }

    #[Test]
    public function reconstitute_restores_from_array()
    {
        $period = ReconciliationPeriod::reconstitute([
            'id' => 1, 'business_id' => 5, 'bank_account_id' => 10,
            'status' => 'completed', 'created_by' => 1,
        ]);

        $this->assertSame('completed', $period->status);
        $this->assertSame(1, $period->createdBy);
    }

    #[Test]
    public function to_array_returns_all_fields()
    {
        $period = new ReconciliationPeriod(id: 1, businessId: 5, bankAccountId: 10, startDate: null, endDate: null, openingBalance: null, closingBalance: null, bookBalance: null, difference: null, status: 'open', createdBy: 1, completedBy: null, completedAt: null, notes: null, createdAt: null, updatedAt: null);
        $array = $period->toArray();

        $this->assertSame(5, $array['business_id']);
        $this->assertSame('open', $array['status']);
    }
}
