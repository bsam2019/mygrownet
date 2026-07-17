<?php

declare(strict_types=1);

namespace App\Extensions\Manufacturing\Entities;

use App\Domain\StockFlow\ValueObjects\CompanyId;
use App\Domain\StockFlow\ValueObjects\ItemId;
use App\Extensions\Manufacturing\ValueObjects\WorkOrderId;
use DateTimeImmutable;
use Illuminate\Contracts\Support\Arrayable;

class WorkOrder implements Arrayable
{
    private function __construct(
        private WorkOrderId $id, private CompanyId $companyId, private ?int $bomId, private ItemId $itemId,
        private string $orderNumber, private float $quantity, private float $completedQuantity,
        private float $scrappedQuantity, private string $status, private ?string $dueDate,
        private ?string $startedAt, private ?string $completedAt, private ?string $notes,
        private DateTimeImmutable $createdAt, private DateTimeImmutable $updatedAt,
    ) {}

    public static function create(CompanyId $companyId, ItemId $itemId, string $orderNumber, float $quantity, ?int $bomId = null, ?string $dueDate = null, ?string $notes = null): self
    {
        return new self(WorkOrderId::generate(), $companyId, $bomId, $itemId, $orderNumber, $quantity, 0, 0, 'draft', $dueDate, null, null, $notes, new DateTimeImmutable(), new DateTimeImmutable());
    }

    public static function reconstitute(WorkOrderId $id, CompanyId $companyId, ?int $bomId, ItemId $itemId, string $orderNumber, float $quantity, float $completedQuantity, float $scrappedQuantity, string $status, ?string $dueDate, ?string $startedAt, ?string $completedAt, ?string $notes, DateTimeImmutable $createdAt, DateTimeImmutable $updatedAt): self
    {
        return new self($id, $companyId, $bomId, $itemId, $orderNumber, $quantity, $completedQuantity, $scrappedQuantity, $status, $dueDate, $startedAt, $completedAt, $notes, $createdAt, $updatedAt);
    }

    public function id(): int { return $this->id->toInt(); }
    public function toArray(): array
    {
        return [
            'id' => $this->id->toInt(), 'sa_company_id' => $this->companyId->toInt(),
            'sa_bom_id' => $this->bomId, 'sa_item_id' => $this->itemId->toInt(),
            'order_number' => $this->orderNumber, 'quantity' => $this->quantity,
            'completed_quantity' => $this->completedQuantity, 'scrapped_quantity' => $this->scrappedQuantity,
            'status' => $this->status, 'due_date' => $this->dueDate, 'started_at' => $this->startedAt,
            'completed_at' => $this->completedAt, 'notes' => $this->notes,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }
}
