<?php

namespace App\Infrastructure\BizDocs\Persistence\Repositories;

use App\Domain\BizDocs\DocumentManagement\Entities\Document;
use App\Domain\BizDocs\DocumentManagement\Entities\DocumentItem;
use App\Domain\BizDocs\DocumentManagement\Entities\DocumentPayment;
use App\Domain\BizDocs\DocumentManagement\Repositories\DocumentRepositoryInterface;
use App\Domain\BizDocs\DocumentManagement\ValueObjects\DocumentNumber;
use App\Domain\BizDocs\DocumentManagement\ValueObjects\DocumentStatus;
use App\Domain\BizDocs\DocumentManagement\ValueObjects\DocumentType;
use App\Domain\BizDocs\DocumentManagement\ValueObjects\Money;
use App\Domain\BizDocs\DocumentManagement\ValueObjects\PaymentMethod;
use App\Infrastructure\BizDocs\Persistence\Eloquent\DocumentItemModel;
use App\Infrastructure\BizDocs\Persistence\Eloquent\DocumentModel;
use App\Infrastructure\BizDocs\Persistence\Eloquent\DocumentPaymentModel;

class EloquentDocumentRepository implements DocumentRepositoryInterface
{
    public function save(Document $document): Document
    {
        $totals = $document->calculateTotals();

        $data = [
            'business_id' => $document->businessId(),
            'customer_id' => $document->customerId(),
            'template_id' => $document->templateId(),
            'document_type' => $document->type()->value(),
            'document_number' => $document->number()->value(),
            'issue_date' => $document->issueDate()->format('Y-m-d'),
            'due_date' => $document->dueDate()?->format('Y-m-d'),
            'validity_date' => $document->validityDate()?->format('Y-m-d'),
            'subtotal' => $totals['subtotal']->amount() / 100,
            'tax_total' => $totals['tax_total']->amount() / 100,
            'discount_total' => $totals['discount_total']->amount() / 100,
            'grand_total' => $totals['grand_total']->amount() / 100,
            'currency' => $document->currency(),
            'status' => $document->status()->value(),
            'notes' => $document->notes(),
            'terms' => $document->terms(),
            'payment_instructions' => $document->paymentInstructions(),
            'pdf_path' => $document->pdfPath(),
            'discount_type' => $document->discountType(),
            'discount_value' => $document->discountValue(),
            'collect_tax' => $document->collectTax(),
            'cancellation_reason' => $document->cancellationReason(),
            'cancelled_at' => $document->cancelledAt()?->format('Y-m-d H:i:s'),
        ];

        if ($document->id()) {
            $model = DocumentModel::findOrFail($document->id());
            $model->update($data);
        } else {
            $model = DocumentModel::create($data);
        }

        // Save items
        DocumentItemModel::where('document_id', $model->id)->delete();
        
        foreach ($document->items() as $item) {
            DocumentItemModel::create([
                'document_id' => $model->id,
                'description' => $item->description(),
                'dimensions' => $item->dimensions(),
                'dimensions_value' => $item->dimensionsValue(),
                'quantity' => $item->quantity(),
                'unit_price' => $item->unitPrice()->amount() / 100,
                'tax_rate' => $item->taxRate(),
                'discount_amount' => $item->discountAmount()->amount() / 100,
                'line_total' => $item->calculateLineTotal()->amount() / 100,
                'sort_order' => $item->sortOrder(),
            ]);
        }

        // Save payments
        foreach ($document->payments() as $payment) {
            if (!$payment->id()) {
                DocumentPaymentModel::create([
                    'document_id' => $model->id,
                    'payment_date' => $payment->paymentDate()->format('Y-m-d'),
                    'amount' => $payment->amount()->amount() / 100,
                    'payment_method' => $payment->paymentMethod()->value(),
                    'reference_number' => $payment->referenceNumber(),
                    'notes' => $payment->notes(),
                    'receipt_id' => $payment->receiptId(),
                ]);
            } else {
                // Update existing payment (in case receipt ID was added)
                DocumentPaymentModel::where('id', $payment->id())->update([
                    'receipt_id' => $payment->receiptId(),
                ]);
            }
        }

        return $this->findById($model->id);
    }

    public function findById(int $id): ?Document
    {
        $model = DocumentModel::with(['items', 'payments', 'customer', 'business'])->find($id);

        if (!$model) {
            return null;
        }

        return $this->toDomainEntity($model);
    }

    public function findByNumber(int $businessId, DocumentNumber $number): ?Document
    {
        $model = DocumentModel::with(['items', 'customer', 'business'])
            ->where('business_id', $businessId)
            ->where('document_number', $number->value())
            ->first();

        if (!$model) {
            return null;
        }

        return $this->toDomainEntity($model);
    }

    public function findByBusiness(int $businessId, ?DocumentType $type = null, int $page = 1, int $perPage = 20): array
    {
        $query = DocumentModel::with(['items', 'customer'])
            ->where('business_id', $businessId);

        if ($type) {
            $query->where('document_type', $type->value());
        }

        $models = $query->orderBy('created_at', 'desc')
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();

        return $models->map(fn($model) => $this->toDomainEntity($model))->all();
    }

    public function findByCustomer(int $customerId, int $page = 1, int $perPage = 20): array
    {
        $models = DocumentModel::with(['items', 'customer', 'business'])
            ->where('customer_id', $customerId)
            ->orderBy('created_at', 'desc')
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();

        return $models->map(fn($model) => $this->toDomainEntity($model))->all();
    }

    public function delete(int $id): bool
    {
        return DocumentModel::where('id', $id)->delete() > 0;
    }

    public function countByBusiness(int $businessId, ?DocumentType $type = null): int
    {
        $query = DocumentModel::where('business_id', $businessId);

        if ($type) {
            $query->where('document_type', $type->value());
        }

        return $query->count();
    }

    private function toDomainEntity(DocumentModel $model): Document
    {
        $items = $model->items->map(function ($itemModel) use ($model) {
            return DocumentItem::fromPersistence(
                $itemModel->id,
                $itemModel->description,
                (float) $itemModel->quantity,
                Money::fromAmount((int) ($itemModel->unit_price * 100), $model->currency),
                (float) $itemModel->tax_rate,
                Money::fromAmount((int) ($itemModel->discount_amount * 100), $model->currency),
                $itemModel->sort_order,
                $itemModel->dimensions,
                (float) ($itemModel->dimensions_value ?? 1.0)
            );
        })->all();

        $payments = $model->payments->map(function ($paymentModel) use ($model) {
            return DocumentPayment::fromPersistence(
                $paymentModel->id,
                $model->id,
                new \DateTimeImmutable($paymentModel->payment_date->format('Y-m-d')),
                Money::fromAmount((int) ($paymentModel->amount * 100), $model->currency),
                PaymentMethod::fromString($paymentModel->payment_method),
                $paymentModel->reference_number,
                $paymentModel->notes,
                $paymentModel->receipt_id
            );
        })->all();

        return Document::fromPersistence(
            $model->id,
            $model->business_id,
            $model->customer_id,
            $model->template_id,
            DocumentType::fromString($model->document_type),
            DocumentNumber::fromString($model->document_number),
            new \DateTimeImmutable($model->issue_date->format('Y-m-d')),
            $model->due_date ? new \DateTimeImmutable($model->due_date->format('Y-m-d')) : null,
            $model->validity_date ? new \DateTimeImmutable($model->validity_date->format('Y-m-d')) : null,
            DocumentStatus::fromString($model->status),
            $model->currency,
            $model->notes,
            $model->terms,
            $model->payment_instructions,
            $model->pdf_path,
            $items,
            $payments,
            $model->discount_type ?? 'amount',
            (float) ($model->discount_value ?? 0),
            (bool) ($model->collect_tax ?? true),
            $model->cancellation_reason,
            $model->cancelled_at ? new \DateTimeImmutable($model->cancelled_at->format('Y-m-d H:i:s')) : null
        );
    }
}
