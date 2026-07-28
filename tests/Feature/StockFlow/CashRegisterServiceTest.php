<?php

namespace Tests\Feature\StockFlow;

class CashRegisterServiceTest extends StockFlowTestCase
{
    public function test_can_open_register(): void
    {
        $register = $this->cashRegisterService->openRegister(
            $this->companyId,
            now()->format('Y-m-d'),
            500.00,
            $this->user->id,
        );

        $this->assertNotNull($register);
        $this->assertGreaterThan(0, $register->id());
        $this->assertTrue($register->isOpen());
        $this->assertEquals(500.00, $register->getOpeningBalance()->toFloat());
    }

    public function test_opening_existing_date_returns_same_register(): void
    {
        $first = $this->cashRegisterService->openRegister(
            $this->companyId,
            '2026-07-28',
            500.00,
            $this->user->id,
        );

        $second = $this->cashRegisterService->openRegister(
            $this->companyId,
            '2026-07-28',
            500.00,
            $this->user->id,
        );

        $this->assertEquals($first->id(), $second->id());
    }

    public function test_cannot_open_already_closed_register(): void
    {
        $this->expectException(\App\Domain\StockFlow\Exceptions\OperationFailedException::class);

        $register = $this->cashRegisterService->openRegister(
            $this->companyId,
            now()->format('Y-m-d'),
            500.00,
            $this->user->id,
        );

        $this->cashRegisterService->closeRegister(
            $register->id(),
            500.00,
        );

        $this->cashRegisterService->openRegister(
            $this->companyId,
            now()->format('Y-m-d'),
            500.00,
            $this->user->id,
        );
    }

    public function test_can_add_expense_movement(): void
    {
        $register = $this->cashRegisterService->openRegister(
            $this->companyId,
            now()->format('Y-m-d'),
            1000.00,
            $this->user->id,
        );

        $this->cashRegisterService->addMovement(
            $this->companyId,
            $register->id(),
            ['type' => 'expense', 'amount' => 200.00],
            $this->user->id,
        );

        $found = $this->cashRegisterService->getRegisterById($register->id(), $this->companyId);
        $this->assertEquals(200.00, $found->getTotalExpenses()->toFloat());
        $this->assertEquals(800.00, $found->getExpectedClosing()->toFloat());
    }

    public function test_can_add_banking_movement(): void
    {
        $register = $this->cashRegisterService->openRegister(
            $this->companyId,
            now()->format('Y-m-d'),
            1000.00,
            $this->user->id,
        );

        $this->cashRegisterService->addMovement(
            $this->companyId,
            $register->id(),
            ['type' => 'banking', 'amount' => 500.00],
            $this->user->id,
        );

        $found = $this->cashRegisterService->getRegisterById($register->id(), $this->companyId);
        $this->assertEquals(500.00, $found->getTotalBanking()->toFloat());
        $this->assertEquals(500.00, $found->getExpectedClosing()->toFloat());
    }

    public function test_can_add_sale_movement(): void
    {
        $register = $this->cashRegisterService->openRegister(
            $this->companyId,
            now()->format('Y-m-d'),
            500.00,
            $this->user->id,
        );

        $this->cashRegisterService->addMovement(
            $this->companyId,
            $register->id(),
            ['type' => 'float_top_up', 'amount' => 300.00],
            $this->user->id,
        );

        $found = $this->cashRegisterService->getRegisterById($register->id(), $this->companyId);
        $this->assertEquals(300.00, $found->getTotalSales()->toFloat());
    }

    public function test_can_close_register_with_exact_amount(): void
    {
        $register = $this->cashRegisterService->openRegister(
            $this->companyId,
            now()->format('Y-m-d'),
            1000.00,
            $this->user->id,
        );

        $closed = $this->cashRegisterService->closeRegister(
            $register->id(),
            1000.00,
        );

        $this->assertFalse($closed->isOpen());
        $this->assertEquals(1000.00, $closed->getActualClosing()->toFloat());
        $this->assertEquals(0, $closed->getVariance()->toFloat());
    }

    public function test_close_register_with_variance(): void
    {
        $register = $this->cashRegisterService->openRegister(
            $this->companyId,
            now()->format('Y-m-d'),
            1000.00,
            $this->user->id,
        );

        $closed = $this->cashRegisterService->closeRegister(
            $register->id(),
            950.00,
        );

        $this->assertEquals(950.00, $closed->getActualClosing()->toFloat());
        $this->assertEquals(-50.00, $closed->getVariance()->toFloat());
    }

    public function test_can_verify_register(): void
    {
        $register = $this->cashRegisterService->openRegister(
            $this->companyId,
            now()->format('Y-m-d'),
            1000.00,
            $this->user->id,
        );

        $closed = $this->cashRegisterService->closeRegister(
            $register->id(),
            1000.00,
        );

        $verified = $this->cashRegisterService->verifyRegister($closed->id());
        $this->assertEquals('verified', $verified->getStatus()->value());
    }

    public function test_get_registers_for_company(): void
    {
        $this->cashRegisterService->openRegister($this->companyId, '2026-07-01', 500.00, $this->user->id);
        $this->cashRegisterService->openRegister($this->companyId, '2026-07-02', 500.00, $this->user->id);

        $registers = $this->cashRegisterService->getRegistersForCompany($this->companyId);
        $this->assertCount(2, $registers);
    }

    public function test_get_register_by_id_returns_null_for_wrong_company(): void
    {
        $register = $this->cashRegisterService->openRegister(
            $this->companyId,
            now()->format('Y-m-d'),
            500.00,
            $this->user->id,
        );

        $found = $this->cashRegisterService->getRegisterById($register->id(), 99999);
        $this->assertNull($found);
    }
}
