<?php

namespace App\Domain\GrowFinance\Entities;

class BankStatement
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $businessId,
        public readonly int $bankAccountId,
        public readonly ?string $statementPeriod = null,
        public readonly ?\DateTimeImmutable $startDate = null,
        public readonly ?\DateTimeImmutable $endDate = null,
        public readonly ?float $openingBalance = null,
        public readonly ?float $closingBalance = null,
        public readonly ?string $fileName = null,
        public readonly ?string $filePath = null,
        public readonly ?int $lineCount = null,
        public readonly ?string $status = null,
        public readonly ?\DateTimeImmutable $createdAt = null,
        public readonly ?\DateTimeImmutable $updatedAt = null,
    ) {}

    public static function reconstitute(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            businessId: (int) $data['business_id'],
            bankAccountId: (int) $data['bank_account_id'],
            statementPeriod: $data['statement_period'] ?? null,
            startDate: isset($data['start_date']) ? new \DateTimeImmutable($data['start_date']) : null,
            endDate: isset($data['end_date']) ? new \DateTimeImmutable($data['end_date']) : null,
            openingBalance: array_key_exists('opening_balance', $data) ? (float) $data['opening_balance'] : null,
            closingBalance: array_key_exists('closing_balance', $data) ? (float) $data['closing_balance'] : null,
            fileName: $data['file_name'] ?? null,
            filePath: $data['file_path'] ?? null,
            lineCount: isset($data['line_count']) ? (int) $data['line_count'] : null,
            status: $data['status'] ?? null,
            createdAt: isset($data['created_at']) ? new \DateTimeImmutable($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? new \DateTimeImmutable($data['updated_at']) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'business_id' => $this->businessId,
            'bank_account_id' => $this->bankAccountId,
            'statement_period' => $this->statementPeriod,
            'start_date' => $this->startDate?->format('Y-m-d H:i:s'),
            'end_date' => $this->endDate?->format('Y-m-d H:i:s'),
            'opening_balance' => $this->openingBalance,
            'closing_balance' => $this->closingBalance,
            'file_name' => $this->fileName,
            'file_path' => $this->filePath,
            'line_count' => $this->lineCount,
            'status' => $this->status,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}