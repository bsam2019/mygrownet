<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Entities;

use App\Domain\StockFlow\ValueObjects\ControlledMedicineId;
use App\Domain\StockFlow\ValueObjects\CompanyId;
use App\Domain\StockFlow\ValueObjects\ItemId;
use App\Domain\StockFlow\ValueObjects\LotId;
use App\Domain\StockFlow\ValueObjects\UserId;
use DateTimeImmutable;
use Illuminate\Contracts\Support\Arrayable;

class ControlledMedicine implements Arrayable
{
    private function __construct(
        private ControlledMedicineId $id, private CompanyId $companyId, private ItemId $itemId,
        private ?LotId $lotId, private string $transactionType, private float $quantity,
        private float $balanceAfter, private ?string $patientName, private ?string $patientIdNumber,
        private ?string $prescriptionNumber, private ?string $notes, private UserId $staffUserId,
        private DateTimeImmutable $createdAt, private DateTimeImmutable $updatedAt,
    ) {}

    public static function create(CompanyId $companyId, ItemId $itemId, string $transactionType, float $quantity, float $balanceAfter, UserId $staffUserId, ?LotId $lotId = null, ?string $patientName = null, ?string $patientIdNumber = null, ?string $prescriptionNumber = null, ?string $notes = null): self
    {
        return new self(ControlledMedicineId::generate(), $companyId, $itemId, $lotId, $transactionType, $quantity, $balanceAfter, $patientName, $patientIdNumber, $prescriptionNumber, $notes, $staffUserId, new DateTimeImmutable(), new DateTimeImmutable());
    }

    public static function reconstitute(ControlledMedicineId $id, CompanyId $companyId, ItemId $itemId, ?LotId $lotId, string $transactionType, float $quantity, float $balanceAfter, ?string $patientName, ?string $patientIdNumber, ?string $prescriptionNumber, ?string $notes, UserId $staffUserId, DateTimeImmutable $createdAt, DateTimeImmutable $updatedAt): self
    {
        return new self($id, $companyId, $itemId, $lotId, $transactionType, $quantity, $balanceAfter, $patientName, $patientIdNumber, $prescriptionNumber, $notes, $staffUserId, $createdAt, $updatedAt);
    }

    public function id(): int { return $this->id->toInt(); }
    public function toArray(): array
    {
        return [
            'id' => $this->id->toInt(), 'sa_company_id' => $this->companyId->toInt(),
            'sa_item_id' => $this->itemId->toInt(), 'sa_lot_id' => $this->lotId?->toInt(),
            'transaction_type' => $this->transactionType, 'quantity' => $this->quantity,
            'balance_after' => $this->balanceAfter, 'patient_name' => $this->patientName,
            'patient_id_number' => $this->patientIdNumber, 'prescription_number' => $this->prescriptionNumber,
            'notes' => $this->notes, 'staff_user_id' => $this->staffUserId->toInt(),
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }
}
