<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Investor\Entities;

use App\Domain\Investor\Entities\InvestorAccount;
use App\Domain\Investor\ValueObjects\InvestorStatus;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class InvestorAccountTest extends TestCase
{
    public function test_create_with_investment_sets_ciu_status(): void
    {
        $account = InvestorAccount::create(
            userId: 1,
            name: 'John Investor',
            email: 'john@investor.com',
            investmentAmount: 50000.00,
            investmentDate: new DateTimeImmutable('2026-01-15'),
            investmentRoundId: 1,
            equityPercentage: 2.5
        );

        $this->assertEquals('John Investor', $account->getName());
        $this->assertEquals('john@investor.com', $account->getEmail());
        $this->assertEquals(50000.00, $account->getInvestmentAmount());
        $this->assertTrue($account->isCIU());
        $this->assertFalse($account->isProspective());
        $this->assertFalse($account->isShareholder());
        $this->assertTrue($account->hasInvested());
        $this->assertEquals(2.5, $account->getEquityPercentage());
        $this->assertEquals(0, $account->getId());
    }

    public function test_create_without_investment_sets_prospective(): void
    {
        $account = InvestorAccount::create(
            userId: null,
            name: 'Prospective Lead',
            email: 'lead@example.com',
            investmentAmount: 0,
            investmentDate: new DateTimeImmutable(),
            investmentRoundId: null,
            equityPercentage: 0
        );

        $this->assertTrue($account->isProspective());
        $this->assertFalse($account->hasInvested());
    }

    public function test_convert_to_shareholder(): void
    {
        $account = $this->createAccount();
        $account->convertToShareholder();

        $this->assertTrue($account->isShareholder());
        $this->assertFalse($account->isCIU());
    }

    public function test_exit(): void
    {
        $account = $this->createAccount();
        $account->exit();

        $this->assertTrue($account->getStatus()->equals(InvestorStatus::exited()));
    }

    public function test_record_investment(): void
    {
        $account = InvestorAccount::create(
            userId: null,
            name: 'New Investor',
            email: 'new@example.com',
            investmentAmount: 0,
            investmentDate: new DateTimeImmutable(),
            investmentRoundId: null,
            equityPercentage: 0
        );

        $account->recordInvestment(100000.00, 2, 5.0);

        $this->assertEquals(100000.00, $account->getInvestmentAmount());
        $this->assertEquals(2, $account->getInvestmentRoundId());
        $this->assertEquals(5.0, $account->getEquityPercentage());
        $this->assertTrue($account->isCIU());
        $this->assertTrue($account->hasInvested());
    }

    public function test_from_persistence_restores_state(): void
    {
        $now = new DateTimeImmutable();
        $status = InvestorStatus::shareholder();

        $account = InvestorAccount::fromPersistence(
            id: 42,
            userId: 7,
            name: 'Persisted User',
            email: 'persisted@example.com',
            investmentAmount: 75000.00,
            investmentDate: $now,
            investmentRoundId: 3,
            status: $status,
            equityPercentage: 1.5,
            createdAt: $now,
            updatedAt: $now
        );

        $this->assertEquals(42, $account->getId());
        $this->assertEquals(7, $account->getUserId());
        $this->assertTrue($account->isShareholder());
        $this->assertEquals(75000.00, $account->getInvestmentAmount());
        $this->assertEquals(3, $account->getInvestmentRoundId());
    }

    private function createAccount(): InvestorAccount
    {
        return InvestorAccount::create(
            userId: 1,
            name: 'Test',
            email: 'test@test.com',
            investmentAmount: 25000,
            investmentDate: new DateTimeImmutable(),
            investmentRoundId: 1,
            equityPercentage: 1.0
        );
    }
}
