<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Entities;

use Illuminate\Contracts\Support\Arrayable;

use App\Domain\StockFlow\ValueObjects\PhysicalCountId;
use App\Domain\StockFlow\ValueObjects\CompanyId;
use DateTimeImmutable;

class PhysicalCount implements Arrayable
{
    private function __construct(
        private PhysicalCountId $id,
        private CompanyId $companyId,
        private string $title,
        private DateTimeImmutable $countDate,
        private string $status,
        private int $countedBy,
        private ?int $verifiedBy,
        private ?string $notes,
        private DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt,
        private array $items = [],
    ) {}

    public static function create(CompanyId $companyId, string $title, DateTimeImmutable $countDate, int $countedBy, ?string $notes = null): self
    {
        return new self(PhysicalCountId::generate(), $companyId, $title, $countDate, 'draft', $countedBy, null, $notes, new DateTimeImmutable(), new DateTimeImmutable());
    }

    public static function reconstitute(PhysicalCountId $id, CompanyId $companyId, string $title, DateTimeImmutable $countDate, string $status, int $countedBy, ?int $verifiedBy, ?string $notes, DateTimeImmutable $createdAt, DateTimeImmutable $updatedAt): self
    {
        return new self($id, $companyId, $title, $countDate, $status, $countedBy, $verifiedBy, $notes, $createdAt, $updatedAt);
    }

    public function complete(int $verifiedBy): void { $this->status = 'completed'; $this->verifiedBy = $verifiedBy; $this->updatedAt = new DateTimeImmutable(); }

    public function addItem(CountItem $item): void { $this->items[] = $item; }
    public function getItems(): array { return $this->items; }

    public function id(): int { return $this->id->toInt(); }
    public function getId(): PhysicalCountId { return $this->id; }
    public function getCompanyId(): CompanyId { return $this->companyId; }
    public function getTitle(): string { return $this->title; }
    public function getCountDate(): DateTimeImmutable { return $this->countDate; }
    public function getStatus(): string { return $this->status; }
    public function isDraft(): bool { return $this->status === 'draft'; }
    public function isCompleted(): bool { return $this->status === 'completed'; }
    public function getCountedBy(): int { return $this->countedBy; }
    public function getVerifiedBy(): ?int { return $this->verifiedBy; }
    public function getNotes(): ?string { return $this->notes; }

    public function toArray(): array
    {
        return [
            'id' => $this->id->toInt(),
            'sa_company_id' => $this->companyId->toInt(),
            'title' => $this->title,
            'count_date' => $this->countDate->format('Y-m-d'),
            'status' => $this->status,
            'counted_by' => $this->countedBy,
            'verified_by' => $this->verifiedBy,
            'notes' => $this->notes,
            'items' => array_map(fn($i) => $i instanceof CountItem ? $i->toArray() : $i, $this->items),
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }
}
