<?php

namespace Tests\Unit\Domain\LifePlus\Entities;

use App\Domain\LifePlus\Entities\LifePlusBudget;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class LifePlusBudgetTest extends TestCase
{
    #[Test]
    public function reconstitute_sets_all_fields()
    {
        $startDate = new DateTimeImmutable('2026-08-01');
        $endDate = new DateTimeImmutable('2026-08-31');
        $createdAt = new DateTimeImmutable('2026-08-01 00:00:00');
        $updatedAt = new DateTimeImmutable('2026-08-15 10:00:00');

        $budget = LifePlusBudget::reconstitute([
            'id' => 1,
            'user_id' => 42,
            'category_id' => 3,
            'amount' => '2000.00',
            'period' => 'monthly',
            'start_date' => '2026-08-01',
            'end_date' => '2026-08-31',
            'created_at' => '2026-08-01 00:00:00',
            'updated_at' => '2026-08-15 10:00:00',
        ]);

        $this->assertSame(1, $budget->id);
        $this->assertSame(42, $budget->userId);
        $this->assertSame(3, $budget->categoryId);
        $this->assertSame(2000.0, $budget->amount);
        $this->assertSame('monthly', $budget->period);
        $this->assertEquals($startDate, $budget->startDate);
        $this->assertEquals($endDate, $budget->endDate);
        $this->assertEquals($createdAt, $budget->createdAt);
        $this->assertEquals($updatedAt, $budget->updatedAt);
    }

    #[Test]
    public function reconstitute_applies_defaults()
    {
        $budget = LifePlusBudget::reconstitute([
            'user_id' => 1,
            'amount' => 500.0,
            'start_date' => '2026-08-01',
        ]);

        $this->assertNull($budget->id);
        $this->assertNull($budget->categoryId);
        $this->assertSame('monthly', $budget->period);
        $this->assertNull($budget->endDate);
        $this->assertNull($budget->createdAt);
        $this->assertNull($budget->updatedAt);
    }

    #[Test]
    public function toArray_round_trips_all_fields()
    {
        $data = [
            'id' => 5,
            'user_id' => 99,
            'category_id' => null,
            'amount' => 1500.0,
            'period' => 'weekly',
            'start_date' => '2026-08-18',
            'end_date' => null,
            'created_at' => '2026-08-18 06:00:00',
            'updated_at' => null,
        ];

        $budget = LifePlusBudget::reconstitute($data);
        $result = $budget->toArray();

        $this->assertSame($data['id'], $result['id']);
        $this->assertSame($data['user_id'], $result['user_id']);
        $this->assertNull($result['category_id']);
        $this->assertSame($data['amount'], $result['amount']);
        $this->assertSame($data['period'], $result['period']);
        $this->assertSame($data['start_date'], $result['start_date']);
        $this->assertNull($result['end_date']);
        $this->assertSame($data['created_at'], $result['created_at']);
        $this->assertNull($result['updated_at']);
    }

    #[Test]
    public function start_date_is_required_and_parsed()
    {
        $budget = LifePlusBudget::reconstitute([
            'user_id' => 1,
            'amount' => 300.0,
            'start_date' => '2026-09-01',
        ]);

        $this->assertEquals('2026-09-01', $budget->startDate->format('Y-m-d'));
        $result = $budget->toArray();
        $this->assertSame('2026-09-01', $result['start_date']);
    }
}
