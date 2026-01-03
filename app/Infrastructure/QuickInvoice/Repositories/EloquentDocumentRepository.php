<?php

declare(strict_types=1);

namespace App\Infrastructure\QuickInvoice\Repositories;

use App\Domain\QuickInvoice\Entities\Document;
use App\Domain\QuickInvoice\Entities\LineItem;
use App\Domain\QuickInvoice\Repositories\DocumentRepositoryInterface;
use App\Domain\QuickInvoice\ValueObjects\BusinessInfo;
use App\Domain\QuickInvoice\ValueObjects\ClientInfo;
use App\Domain\QuickInvoice\ValueObjects\DocumentId;
use App\Domain\QuickInvoice\ValueObjects\DocumentNumber;
use App\Domain\QuickInvoice\ValueObjects\DocumentType;
use App\Domain\QuickInvoice\ValueObjects\TemplateStyle;
use App\Domain\QuickInvoice\ValueObjects\ThemeColors;
use App\Models\QuickInvoiceDocument;
use Carbon\Carbon;

class EloquentDocumentRepository implements DocumentRepositoryInterface
{
    public function findById(DocumentId $id): ?Document
    {
        $model = QuickInvoiceDocument::with('items')->find($id->value());
        
        if (!$model) {
            return null;
        }

        return $this->toDomainEntity($model);
    }

    public function findBySessionId(string $sessionId): array
    {
        $models = QuickInvoiceDocument::with('items')
            ->where('session_id', $sessionId)
            ->orderBy('created_at', 'desc')
            ->get();

        return $models->map(fn($model) => $this->toDomainEntity($model))->toArray();
    }

    public function findByUserId(int $userId): array
    {
        $models = QuickInvoiceDocument::with('items')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();

        return $models->map(fn($model) => $this->toDomainEntity($model))->toArray();
    }

    public function save(Document $document): Document
    {
        $data = $document->toArray();
        $documentId = $document->id()->value();
        
        // Check if document exists
        $model = QuickInvoiceDocument::find($documentId);
        
        if (!$model) {
            // Create new - explicitly set ID to match domain entity
            $model = new QuickInvoiceDocument();
            $model->id = $documentId;
        }
        
        // Update all fields
        $model->fill([
            'user_id' => $data['user_id'],
            'session_id' => $data['session_id'],
            'document_type' => $data['type'],
            'document_number' => $data['document_number'],
            'business_name' => $data['business_info']['name'],
            'business_address' => $data['business_info']['address'],
            'business_phone' => $data['business_info']['phone'],
            'business_email' => $data['business_info']['email'],
            'business_logo' => $data['business_info']['logo'],
            'business_tax_number' => $data['business_info']['tax_number'],
            'business_website' => $data['business_info']['website'],
            'client_name' => $data['client_info']['name'],
            'client_address' => $data['client_info']['address'],
            'client_phone' => $data['client_info']['phone'],
            'client_email' => $data['client_info']['email'],
            'issue_date' => $data['issue_date'],
            'due_date' => $data['due_date'],
            'currency' => $data['currency'],
            'subtotal' => $data['subtotal'],
            'tax_rate' => $data['tax_rate'],
            'tax_amount' => $data['tax_amount'],
            'discount_rate' => $data['discount_rate'],
            'discount_amount' => $data['discount_amount'],
            'total' => $data['total'],
            'notes' => $data['notes'],
            'terms' => $data['terms'],
            'status' => $data['status'],
            'template' => $data['template'],
            'colors' => $data['colors'],
            'signature' => $data['signature'],
            'prepared_by' => $data['prepared_by'] ?? null,
        ]);
        
        $model->save();

        // Sync line items
        $model->items()->delete();
        foreach ($data['items'] as $item) {
            $model->items()->create([
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'unit' => $item['unit'],
                'unit_price' => $item['unit_price'],
                'amount' => $item['amount'],
                'sort_order' => $item['sort_order'],
            ]);
        }

        return $this->toDomainEntity($model->fresh(['items']));
    }

    public function delete(DocumentId $id): bool
    {
        $model = QuickInvoiceDocument::find($id->value());
        
        if (!$model) {
            return false;
        }

        $model->items()->delete();
        return $model->delete();
    }

    public function exists(DocumentId $id): bool
    {
        return QuickInvoiceDocument::where('id', $id->value())->exists();
    }

    public function canAccess(DocumentId $id, ?int $userId, ?string $sessionId): bool
    {
        $document = $this->findById($id);
        
        if (!$document) {
            return false;
        }

        return $document->canBeAccessedBy($userId, $sessionId);
    }

    /**
     * Convert Eloquent model to Domain entity
     */
    private function toDomainEntity(QuickInvoiceDocument $model): Document
    {
        $businessInfo = BusinessInfo::create(
            name: $model->business_name,
            address: $model->business_address,
            phone: $model->business_phone,
            email: $model->business_email,
            logo: $model->business_logo,
            taxNumber: $model->business_tax_number,
            website: $model->business_website
        );

        $clientInfo = ClientInfo::create(
            name: $model->client_name,
            address: $model->client_address,
            phone: $model->client_phone,
            email: $model->client_email
        );

        $items = $model->items->map(function ($item) use ($model) {
            return LineItem::fromArray([
                'id' => $item->id,
                'description' => $item->description,
                'quantity' => $item->quantity,
                'unit' => $item->unit,
                'unit_price' => $item->unit_price,
                'sort_order' => $item->sort_order,
            ], $model->currency);
        })->toArray();

        return Document::reconstitute(
            id: DocumentId::fromString($model->id),
            userId: $model->user_id,
            sessionId: $model->session_id,
            type: DocumentType::from($model->document_type),
            documentNumber: DocumentNumber::fromString($model->document_number),
            businessInfo: $businessInfo,
            clientInfo: $clientInfo,
            issueDate: Carbon::parse($model->issue_date),
            dueDate: $model->due_date ? Carbon::parse($model->due_date) : null,
            currency: $model->currency,
            items: $items,
            taxRate: (float) $model->tax_rate,
            discountRate: (float) $model->discount_rate,
            notes: $model->notes,
            terms: $model->terms,
            status: $model->status,
            createdAt: Carbon::parse($model->created_at),
            updatedAt: Carbon::parse($model->updated_at),
            template: $model->template ? TemplateStyle::tryFrom($model->template) : null,
            colors: $model->colors ? ThemeColors::fromArray($model->colors) : null,
            signature: $model->signature,
            preparedBy: $model->prepared_by
        );
    }
}
