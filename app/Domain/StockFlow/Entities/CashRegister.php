<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Entities;

use Illuminate\Contracts\Support\Arrayable;

use App\Domain\StockFlow\ValueObjects\CashRegisterId;
use App\Domain\StockFlow\ValueObjects\CompanyId;
use App\Domain\StockFlow\ValueObjects\Money;
use App\Domain\StockFlow\ValueObjects\CashRegisterStatus;
use DateTimeImmutable;

class CashRegister implements Arrayable
{
    private function __construct(
        private CashRegisterId $id,
        private CompanyId $companyId,
        private DateTimeImmutable $registerDate,
        private CashRegisterStatus $status,
        private Money $openingBalance,
        private Money $totalSales,
        private Money $totalExpenses,
        private Money $totalBanking,
        private Money $expectedClosing,
        private ?Money $actualClosing,
        private ?Money $variance,
        private ?string $notes,
        private int $openedBy,
        private ?int $closedBy,
        private DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt,
    ) {}

    public static function create(CompanyId $companyId, DateTimeImmutable $registerDate, Money $openingBalance, int $openedBy): self
    {
        return new self(
            CashRegisterId::generate(), $companyId, $registerDate, CashRegisterStatus::open(),
            $openingBalance, Money::zero(), Money::zero(), Money::zero(),
            $openingBalance, null, null, null, $openedBy, null,
            new DateTimeImmutable(), new DateTimeImmutable(),
        );
    }

    public static function reconstitute(
        CashRegisterId $id, CompanyId $companyId, DateTimeImmutable $registerDate,
        CashRegisterStatus $status, Money $openingBalance, Money $totalSales,
        Money $totalExpenses, Money $totalBanking, Money $expectedClosing,
        ?Money $actualClosing, ?Money $variance, ?string $notes,
        int $openedBy, ?int $closedBy,
        DateTimeImmutable $createdAt, DateTimeImmutable $updatedAt,
    ): self {
        return new self($id, $companyId, $registerDate, $status, $openingBalance,
            $totalSales, $totalExpenses, $totalBanking, $expectedClosing,
            $actualClosing, $variance, $notes, $openedBy, $closedBy,
            $createdAt, $updatedAt);
    }

    public function recordSale(Money $amount): void
    {
        $this->totalSales = $this->totalSales->add($amount);
        $this->recalculateExpectedClosing();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function addExpense(Money $amount): void
    {
        $this->totalExpenses = $this->totalExpenses->add($amount);
        $this->recalculateExpectedClosing();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function addBanking(Money $amount): void
    {
        $this->totalBanking = $this->totalBanking->add($amount);
        $this->recalculateExpectedClosing();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function close(Money $actualClosing, ?string $notes = null, ?int $closedBy = null): void
    {
        $this->actualClosing = $actualClosing;
        $this->variance = $actualClosing->subtract($this->expectedClosing);
        $this->status = CashRegisterStatus::closed();
        $this->notes = $notes;
        $this->closedBy = $closedBy;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function verify(): void
    {
        $this->status = CashRegisterStatus::verified();
        $this->updatedAt = new DateTimeImmutable();
    }

    private function recalculateExpectedClosing(): void
    {
        $expected = $this->openingBalance
            ->add($this->totalSales)
            ->subtract($this->totalExpenses)
            ->subtract($this->totalBanking);
        $this->expectedClosing = $expected;
    }

    public function id(): int { return $this->id->toInt(); }
    public function getId(): CashRegisterId { return $this->id; }
    public function getCompanyId(): CompanyId { return $this->companyId; }
    public function getRegisterDate(): DateTimeImmutable { return $this->registerDate; }
    public function getStatus(): CashRegisterStatus { return $this->status; }
    public function getOpeningBalance(): Money { return $this->openingBalance; }
    public function getTotalSales(): Money { return $this->totalSales; }
    public function getTotalExpenses(): Money { return $this->totalExpenses; }
    public function getTotalBanking(): Money { return $this->totalBanking; }
    public function getExpectedClosing(): Money { return $this->expectedClosing; }
    public function getActualClosing(): ?Money { return $this->actualClosing; }
    public function getVariance(): ?Money { return $this->variance; }
    public function getNotes(): ?string { return $this->notes; }
    public function getOpenedBy(): int { return $this->openedBy; }
    public function getClosedBy(): ?int { return $this->closedBy; }
    public function isOpen(): bool { return $this->status->isOpen(); }

    public function toArray(): array
    {
        return [
            'id' => $this->id->toInt(),
            'sa_company_id' => $this->companyId->toInt(),
            'register_date' => $this->registerDate->format('Y-m-d'),
            'status' => $this->status->value(),
            'opening_balance' => $this->openingBalance->toFloat(),
            'total_sales' => $this->totalSales->toFloat(),
            'total_expenses' => $this->totalExpenses->toFloat(),
            'total_banking' => $this->totalBanking->toFloat(),
            'expected_closing' => $this->expectedClosing->toFloat(),
            'actual_closing' => $this->actualClosing?->toFloat(),
            'variance' => $this->variance?->toFloat(),
            'notes' => $this->notes,
            'opened_by' => $this->openedBy,
            'closed_by' => $this->closedBy,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }
}
