<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Services;

use App\Domain\StockFlow\Entities\CashRegister;
use App\Domain\StockFlow\Events\CashDiscrepancyDetected;
use App\Domain\StockFlow\Exceptions\OperationFailedException;
use App\Domain\StockFlow\Repositories\CashRegisterRepositoryInterface;
use App\Domain\StockFlow\ValueObjects\CashRegisterId;
use App\Domain\StockFlow\ValueObjects\CompanyId;
use App\Domain\StockFlow\ValueObjects\Money;
use DateTimeImmutable;
use Illuminate\Support\Facades\Event;
use Throwable;

class CashRegisterService
{
    private const DISCREPANCY_TOLERANCE = 0.01;

    public function __construct(
        private CashRegisterRepositoryInterface $cashRegisterRepository,
    ) {}

    public function openRegister(int $companyId, string $date, float $openingBalance, int $userId): CashRegister
    {
        $registerDate = new DateTimeImmutable($date);
        $existing = $this->cashRegisterRepository->findByDate(CompanyId::fromInt($companyId), $registerDate);

        if ($existing) {
            if (!$existing->isOpen()) {
                throw new OperationFailedException('open register', 'Register is already closed for this date');
            }
            return $existing;
        }

        $register = CashRegister::create(
            companyId: CompanyId::fromInt($companyId),
            registerDate: $registerDate,
            openingBalance: Money::fromFloat($openingBalance),
            openedBy: $userId,
        );

        return $this->cashRegisterRepository->save($register);
    }

    public function addMovement(int $companyId, int $registerId, array $data, int $userId): void
    {
        $register = $this->cashRegisterRepository->findById(CashRegisterId::fromInt($registerId));
        if (!$register || !$register->isOpen()) {
            throw new OperationFailedException('add movement', 'Register not found or not open');
        }

        $amount = Money::fromFloat((float) $data['amount']);
        $type = $data['type'] ?? '';

        if ($type === 'expense' || $type === 'petty_cash' || $type === 'float_withdrawal') {
            $register->addExpense($amount);
        } elseif ($type === 'banking') {
            $register->addBanking($amount);
        } elseif ($type === 'float_top_up') {
            $register->recordSale($amount);
        }

        $this->cashRegisterRepository->save($register);
    }

    public function closeRegister(int $registerId, float $actualClosing, ?string $notes = null, ?int $closedBy = null): CashRegister
    {
        $register = $this->cashRegisterRepository->findById(CashRegisterId::fromInt($registerId));
        if (!$register) {
            throw new OperationFailedException('close register', 'Register not found');
        }

        $register->close(Money::fromFloat($actualClosing), $notes, $closedBy);
        $saved = $this->cashRegisterRepository->save($register);

        // Detect and signal cash discrepancy
        $variance = $saved->getVariance();
        if ($variance && abs($variance->toFloat()) > self::DISCREPANCY_TOLERANCE) {
            Event::dispatch(new CashDiscrepancyDetected(
                companyId: $saved->getCompanyId()->toInt(),
                cashRegisterId: $registerId,
                registerDate: $saved->getRegisterDate()->format('Y-m-d'),
                expectedAmount: $saved->getExpectedClosing()->toFloat(),
                countedAmount: $actualClosing,
                variance: $variance->toFloat(),
                closedBy: $saved->getClosedBy() ?? 0,
            ));
        }

        return $saved;
    }

    public function verifyRegister(int $registerId): CashRegister
    {
        $register = $this->cashRegisterRepository->findById(CashRegisterId::fromInt($registerId));
        if (!$register) {
            throw new OperationFailedException('verify register', 'Register not found');
        }

        $register->verify();
        return $this->cashRegisterRepository->save($register);
    }

    public function getRegisterById(int $registerId, int $companyId): ?CashRegister
    {
        $register = $this->cashRegisterRepository->findById(CashRegisterId::fromInt($registerId));
        if ($register && $register->getCompanyId()->toInt() !== $companyId) {
            return null;
        }
        return $register;
    }

    public function getRegistersForCompany(int $companyId): array
    {
        return $this->cashRegisterRepository->findByCompanyId(CompanyId::fromInt($companyId));
    }
}
