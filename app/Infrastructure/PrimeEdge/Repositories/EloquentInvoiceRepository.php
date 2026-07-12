<?php

namespace App\Infrastructure\PrimeEdge\Repositories;

use App\Domain\PrimeEdge\Entities\Invoice;
use App\Domain\PrimeEdge\Entities\InvoiceLineItem;
use App\Domain\PrimeEdge\Repositories\InvoiceRepositoryInterface;
use App\Domain\PrimeEdge\ValueObjects\InvoiceId;
use App\Domain\PrimeEdge\ValueObjects\InvoiceNumber;
use App\Domain\PrimeEdge\ValueObjects\InvoiceStatus;
use App\Domain\PrimeEdge\ValueObjects\ClientId;
use App\Domain\PrimeEdge\ValueObjects\Money;
use App\Infrastructure\PrimeEdge\Persistence\InvoiceModel;
use App\Infrastructure\PrimeEdge\Persistence\InvoiceLineItemModel;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;

class EloquentInvoiceRepository implements InvoiceRepositoryInterface
{
    public function save(Invoice $invoice): void
    {
        DB::transaction(function () use ($invoice) {
            InvoiceModel::updateOrCreate(
                ['id' => $invoice->id()->toString()],
                [
                    'client_id' => $invoice->clientId()->toString(),
                    'number' => $invoice->number()->toString(),
                    'status' => $invoice->status()->value,
                    'total_amount' => $invoice->total()->amount(),
                    'total_currency' => $invoice->total()->currency(),
                    'engagement_id' => $invoice->engagementId(),
                    'notes' => $invoice->notes(),
                    'sent_at' => $invoice->sentAt(),
                    'paid_at' => $invoice->paidAt(),
                ]
            );

            InvoiceLineItemModel::where('invoice_id', $invoice->id()->toString())->delete();
            foreach ($invoice->lineItems() as $item) {
                InvoiceLineItemModel::create([
                    'id' => \Ramsey\Uuid\Uuid::uuid4()->toString(),
                    'invoice_id' => $invoice->id()->toString(),
                    'description' => $item->description(),
                    'unit_price_amount' => $item->unitPrice()->amount(),
                    'unit_price_currency' => $item->unitPrice()->currency(),
                    'quantity' => $item->quantity(),
                    'total_amount' => $item->total()->amount(),
                    'total_currency' => $item->total()->currency(),
                ]);
            }
        });
    }

    public function findById(InvoiceId $id): ?Invoice
    {
        $model = InvoiceModel::with('lineItems')->find($id->toString());
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findByClientId(ClientId $clientId): array
    {
        return InvoiceModel::with('lineItems')
            ->where('client_id', $clientId->toString())
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($m) => $this->toDomainEntity($m))
            ->all();
    }

    public function findOverdue(): array
    {
        return InvoiceModel::with('lineItems')
            ->whereIn('status', ['sent', 'overdue'])
            ->where('created_at', '<', now()->subDays(30))
            ->get()
            ->map(fn($m) => $this->toDomainEntity($m))
            ->all();
    }

    public function findAll(): array
    {
        return InvoiceModel::with('lineItems')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($m) => $this->toDomainEntity($m))
            ->all();
    }

    public function nextId(): InvoiceId
    {
        return InvoiceId::generate();
    }

    public function nextSequenceNumber(int $year): int
    {
        $prefix = InvoiceNumber::prefix();
        $last = InvoiceModel::where('number', 'like', "{$prefix}-{$year}-%")
            ->orderBy('number', 'desc')
            ->value('number');

        if (!$last) {
            return 1;
        }

        $parts = explode('-', $last);
        return ((int) end($parts)) + 1;
    }

    private function toDomainEntity(InvoiceModel $model): Invoice
    {
        $lineItems = $model->lineItems->map(fn($li) => InvoiceLineItem::reconstitute(
            description: $li->description,
            unitPrice: $li->unit_price_currency === 'ZMW'
                ? Money::fromKwacha((float) $li->unit_price_amount)
                : Money::fromUsd((float) $li->unit_price_amount),
            quantity: $li->quantity,
        ))->all();

        return Invoice::reconstitute(
            id: InvoiceId::fromString($model->id),
            clientId: ClientId::fromString($model->client_id),
            number: InvoiceNumber::fromString($model->number),
            status: InvoiceStatus::from($model->status),
            total: $model->total_currency === 'ZMW'
                ? Money::fromKwacha((float) $model->total_amount)
                : Money::fromUsd((float) $model->total_amount),
            engagementId: $model->engagement_id,
            notes: $model->notes,
            createdAt: new DateTimeImmutable($model->created_at->toDateTimeString()),
            sentAt: $model->sent_at ? new DateTimeImmutable($model->sent_at->toDateTimeString()) : null,
            paidAt: $model->paid_at ? new DateTimeImmutable($model->paid_at->toDateTimeString()) : null,
            updatedAt: $model->updated_at ? new DateTimeImmutable($model->updated_at->toDateTimeString()) : null,
            lineItems: $lineItems,
        );
    }
}
