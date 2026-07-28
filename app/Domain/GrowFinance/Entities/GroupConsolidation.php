<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\Entities;

class GroupConsolidation
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $groupId,
        public readonly int $businessId,
        public readonly string $period,
        public readonly array $consolidatedData = [],
        public readonly string $functionalCurrency = 'ZMW',
        public readonly string $reportingCurrency = 'ZMW',
        public readonly float $exchangeRate = 1.0,
        public readonly array $eliminationEntries = [],
        public readonly string $status = 'draft',
        public readonly ?string $consolidatedAt = null,
        public readonly ?string $createdAt = null,
        public readonly ?string $updatedAt = null,
    ) {}

    public static function create(
        int $groupId,
        int $businessId,
        string $period,
        string $functionalCurrency = 'ZMW',
        string $reportingCurrency = 'ZMW',
        float $exchangeRate = 1.0,
    ): self {
        return new self(
            id: null, groupId: $groupId, businessId: $businessId, period: $period,
            functionalCurrency: $functionalCurrency, reportingCurrency: $reportingCurrency,
            exchangeRate: $exchangeRate,
        );
    }

    public static function reconstitute(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            groupId: (int) $data['group_id'],
            businessId: (int) $data['business_id'],
            period: $data['period'],
            consolidatedData: json_decode($data['consolidated_data'] ?? '{}', true),
            functionalCurrency: $data['functional_currency'] ?? 'ZMW',
            reportingCurrency: $data['reporting_currency'] ?? 'ZMW',
            exchangeRate: (float) ($data['exchange_rate'] ?? 1.0),
            eliminationEntries: json_decode($data['elimination_entries'] ?? '[]', true),
            status: $data['status'] ?? 'draft',
            consolidatedAt: $data['consolidated_at'] ?? null,
            createdAt: $data['created_at'] ?? null,
            updatedAt: $data['updated_at'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'group_id' => $this->groupId,
            'business_id' => $this->businessId,
            'period' => $this->period,
            'consolidated_data' => json_encode($this->consolidatedData),
            'functional_currency' => $this->functionalCurrency,
            'reporting_currency' => $this->reportingCurrency,
            'exchange_rate' => $this->exchangeRate,
            'elimination_entries' => json_encode($this->eliminationEntries),
            'status' => $this->status,
            'consolidated_at' => $this->consolidatedAt,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }

    public function complete(string $consolidatedAt = null): self
    {
        return new self(
            id: $this->id, groupId: $this->groupId, businessId: $this->businessId,
            period: $this->period, consolidatedData: $this->consolidatedData,
            functionalCurrency: $this->functionalCurrency, reportingCurrency: $this->reportingCurrency,
            exchangeRate: $this->exchangeRate, eliminationEntries: $this->eliminationEntries,
            status: 'completed',
            consolidatedAt: $consolidatedAt ?? date('Y-m-d H:i:s'),
            createdAt: $this->createdAt, updatedAt: $this->updatedAt,
        );
    }
}
