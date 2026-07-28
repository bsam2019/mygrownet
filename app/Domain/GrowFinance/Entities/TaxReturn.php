<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\Entities;

use App\Domain\GrowFinance\ValueObjects\TaxReturnStatus;
use DateTimeImmutable;

class TaxReturn
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $businessId,
        public readonly string $returnType,
        public readonly string $periodLabel,
        public readonly DateTimeImmutable $periodStart,
        public readonly DateTimeImmutable $periodEnd,
        public readonly ?DateTimeImmutable $dueDate = null,
        public readonly float $outputVat = 0.0,
        public readonly float $inputVat = 0.0,
        public readonly float $netVatPayable = 0.0,
        public readonly float $totalSales = 0.0,
        public readonly float $totalPurchases = 0.0,
        public readonly float $withholdingCollected = 0.0,
        public readonly float $withholdingPaid = 0.0,
        public readonly TaxReturnStatus $status = TaxReturnStatus::DRAFT,
        public readonly ?DateTimeImmutable $filedAt = null,
        public readonly ?string $zraReference = null,
        public readonly ?DateTimeImmutable $submittedAt = null,
        public readonly ?string $notes = null,
        public readonly ?DateTimeImmutable $createdAt = null,
        public readonly ?DateTimeImmutable $updatedAt = null,
    ) {}

    public static function reconstitute(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            businessId: (int) $data['business_id'],
            returnType: $data['return_type'],
            periodLabel: $data['period_label'],
            periodStart: new DateTimeImmutable($data['period_start']),
            periodEnd: new DateTimeImmutable($data['period_end']),
            dueDate: isset($data['due_date']) ? new DateTimeImmutable($data['due_date']) : null,
            outputVat: (float) ($data['output_vat'] ?? 0),
            inputVat: (float) ($data['input_vat'] ?? 0),
            netVatPayable: (float) ($data['net_vat_payable'] ?? 0),
            totalSales: (float) ($data['total_sales'] ?? 0),
            totalPurchases: (float) ($data['total_purchases'] ?? 0),
            withholdingCollected: (float) ($data['withholding_collected'] ?? 0),
            withholdingPaid: (float) ($data['withholding_paid'] ?? 0),
            status: TaxReturnStatus::tryFrom($data['status'] ?? 'draft') ?? TaxReturnStatus::DRAFT,
            filedAt: isset($data['filed_at']) ? new DateTimeImmutable($data['filed_at']) : null,
            zraReference: $data['zra_reference'] ?? null,
            submittedAt: isset($data['submitted_at']) ? new DateTimeImmutable($data['submitted_at']) : null,
            notes: $data['notes'] ?? null,
            createdAt: isset($data['created_at']) ? new DateTimeImmutable($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? new DateTimeImmutable($data['updated_at']) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'business_id' => $this->businessId,
            'return_type' => $this->returnType,
            'period_label' => $this->periodLabel,
            'period_start' => $this->periodStart->format('Y-m-d'),
            'period_end' => $this->periodEnd->format('Y-m-d'),
            'due_date' => $this->dueDate?->format('Y-m-d'),
            'output_vat' => $this->outputVat,
            'input_vat' => $this->inputVat,
            'net_vat_payable' => $this->netVatPayable,
            'total_sales' => $this->totalSales,
            'total_purchases' => $this->totalPurchases,
            'withholding_collected' => $this->withholdingCollected,
            'withholding_paid' => $this->withholdingPaid,
            'status' => $this->status->value,
            'filed_at' => $this->filedAt?->format('Y-m-d H:i:s'),
            'zra_reference' => $this->zraReference,
            'submitted_at' => $this->submittedAt?->format('Y-m-d H:i:s'),
            'notes' => $this->notes,
        ];
    }
}
