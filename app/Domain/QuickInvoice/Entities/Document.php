<?php

declare(strict_types=1);

namespace App\Domain\QuickInvoice\Entities;

use App\Domain\QuickInvoice\ValueObjects\BusinessInfo;
use App\Domain\QuickInvoice\ValueObjects\ClientInfo;
use App\Domain\QuickInvoice\ValueObjects\DocumentId;
use App\Domain\QuickInvoice\ValueObjects\DocumentNumber;
use App\Domain\QuickInvoice\ValueObjects\DocumentType;
use App\Domain\QuickInvoice\ValueObjects\Money;
use App\Domain\QuickInvoice\ValueObjects\TemplateStyle;
use App\Domain\QuickInvoice\ValueObjects\ThemeColors;
use Carbon\Carbon;

/**
 * Document Aggregate Root
 */
class Document
{
    private DocumentId $id;
    private ?int $userId;
    private string $sessionId;
    private DocumentType $type;
    private DocumentNumber $documentNumber;
    private BusinessInfo $businessInfo;
    private ClientInfo $clientInfo;
    private Carbon $issueDate;
    private ?Carbon $dueDate;
    private string $currency;
    /** @var LineItem[] */
    private array $items;
    private float $taxRate;
    private float $discountRate;
    private ?string $notes;
    private ?string $terms;
    private string $status;
    private Carbon $createdAt;
    private Carbon $updatedAt;

    // Template & Styling
    private TemplateStyle $template;
    private ThemeColors $colors;
    private ?string $signature;
    private ?string $preparedBy;

    // Calculated values
    private Money $subtotal;
    private Money $taxAmount;
    private Money $discountAmount;
    private Money $total;

    private function __construct()
    {
        $this->items = [];
        $this->taxRate = 0;
        $this->discountRate = 0;
        $this->notes = null;
        $this->terms = null;
        $this->status = 'draft';
        $this->template = TemplateStyle::CLASSIC;
        $this->colors = ThemeColors::default();
        $this->signature = null;
    }

    /**
     * Factory method to create a new document
     */
    public static function create(
        DocumentType $type,
        BusinessInfo $businessInfo,
        ClientInfo $clientInfo,
        string $currency = 'ZMW',
        ?int $userId = null,
        ?string $sessionId = null,
        ?TemplateStyle $template = null,
        ?ThemeColors $colors = null
    ): self {
        $document = new self();
        $document->id = DocumentId::generate();
        $document->userId = $userId;
        $document->sessionId = $sessionId ?? session()->getId();
        $document->type = $type;
        $document->documentNumber = DocumentNumber::generate($type);
        $document->businessInfo = $businessInfo;
        $document->clientInfo = $clientInfo;
        $document->currency = $currency;
        $document->issueDate = Carbon::today();
        $document->dueDate = $type->showDueDate() ? Carbon::today()->addDays(30) : null;
        $document->template = $template ?? TemplateStyle::CLASSIC;
        $document->colors = $colors ?? ThemeColors::default();
        $document->signature = null;
        $document->preparedBy = null;
        $document->createdAt = Carbon::now();
        $document->updatedAt = Carbon::now();
        $document->recalculate();

        return $document;
    }

    /**
     * Reconstitute a document from persistence
     */
    public static function reconstitute(
        DocumentId $id,
        ?int $userId,
        string $sessionId,
        DocumentType $type,
        DocumentNumber $documentNumber,
        BusinessInfo $businessInfo,
        ClientInfo $clientInfo,
        Carbon $issueDate,
        ?Carbon $dueDate,
        string $currency,
        array $items,
        float $taxRate,
        float $discountRate,
        ?string $notes,
        ?string $terms,
        string $status,
        Carbon $createdAt,
        Carbon $updatedAt,
        ?TemplateStyle $template = null,
        ?ThemeColors $colors = null,
        ?string $signature = null,
        ?string $preparedBy = null
    ): self {
        $document = new self();
        $document->id = $id;
        $document->userId = $userId;
        $document->sessionId = $sessionId;
        $document->type = $type;
        $document->documentNumber = $documentNumber;
        $document->businessInfo = $businessInfo;
        $document->clientInfo = $clientInfo;
        $document->issueDate = $issueDate;
        $document->dueDate = $dueDate;
        $document->currency = $currency;
        $document->items = $items;
        $document->taxRate = $taxRate;
        $document->discountRate = $discountRate;
        $document->notes = $notes;
        $document->terms = $terms;
        $document->status = $status;
        $document->template = $template ?? TemplateStyle::CLASSIC;
        $document->colors = $colors ?? ThemeColors::default();
        $document->signature = $signature;
        $document->preparedBy = $preparedBy;
        $document->createdAt = $createdAt;
        $document->updatedAt = $updatedAt;
        $document->recalculate();

        return $document;
    }

    // Item management
    public function addItem(LineItem $item): void
    {
        $this->items[] = $item;
        $this->recalculate();
        $this->touch();
    }

    public function removeItem(string $itemId): void
    {
        $this->items = array_values(array_filter(
            $this->items,
            fn(LineItem $item) => $item->id() !== $itemId
        ));
        $this->recalculate();
        $this->touch();
    }

    public function setItems(array $items): void
    {
        $this->items = [];
        foreach ($items as $index => $itemData) {
            if ($itemData instanceof LineItem) {
                $this->items[] = $itemData;
            } else {
                $this->items[] = LineItem::fromArray(
                    array_merge($itemData, ['sort_order' => $index]),
                    $this->currency
                );
            }
        }
        $this->recalculate();
        $this->touch();
    }

    // Setters
    public function setTaxRate(float $rate): void
    {
        $this->taxRate = max(0, min(100, $rate));
        $this->recalculate();
        $this->touch();
    }

    public function setDiscountRate(float $rate): void
    {
        $this->discountRate = max(0, min(100, $rate));
        $this->recalculate();
        $this->touch();
    }

    public function setNotes(?string $notes): void { $this->notes = $notes; $this->touch(); }
    public function setTerms(?string $terms): void { $this->terms = $terms; $this->touch(); }
    public function setDueDate(?Carbon $date): void { $this->dueDate = $date; $this->touch(); }
    public function setIssueDate(Carbon $date): void { $this->issueDate = $date; $this->touch(); }
    public function setDocumentNumber(DocumentNumber $number): void { $this->documentNumber = $number; $this->touch(); }

    // Template & Styling setters
    public function setTemplate(TemplateStyle $template): void { $this->template = $template; $this->touch(); }
    public function setColors(ThemeColors $colors): void { $this->colors = $colors; $this->touch(); }
    public function setSignature(?string $signature): void { $this->signature = $signature; $this->touch(); }

    // Status changes
    public function markAsSent(): void { $this->status = 'sent'; $this->touch(); }
    public function markAsPaid(): void { $this->status = 'paid'; $this->touch(); }
    public function cancel(): void { $this->status = 'cancelled'; $this->touch(); }

    private function touch(): void { $this->updatedAt = Carbon::now(); }

    private function recalculate(): void
    {
        $subtotalAmount = 0;
        foreach ($this->items as $item) {
            $subtotalAmount += $item->amount()->amount();
        }
        $this->subtotal = Money::create($subtotalAmount, $this->currency);
        $this->discountAmount = $this->subtotal->percentage($this->discountRate);
        $afterDiscount = $this->subtotal->subtract($this->discountAmount);
        $this->taxAmount = $afterDiscount->percentage($this->taxRate);
        $this->total = $afterDiscount->add($this->taxAmount);
    }

    public function canBeAccessedBy(?int $userId, ?string $sessionId): bool
    {
        if ($userId !== null && $this->userId === $userId) return true;
        if ($sessionId !== null && $this->sessionId === $sessionId) return true;
        return false;
    }

    // Getters
    public function id(): DocumentId { return $this->id; }
    public function userId(): ?int { return $this->userId; }
    public function sessionId(): string { return $this->sessionId; }
    public function type(): DocumentType { return $this->type; }
    public function documentNumber(): DocumentNumber { return $this->documentNumber; }
    public function businessInfo(): BusinessInfo { return $this->businessInfo; }
    public function clientInfo(): ClientInfo { return $this->clientInfo; }
    public function issueDate(): Carbon { return $this->issueDate; }
    public function dueDate(): ?Carbon { return $this->dueDate; }
    public function currency(): string { return $this->currency; }
    /** @return LineItem[] */
    public function items(): array { return $this->items; }
    public function taxRate(): float { return $this->taxRate; }
    public function discountRate(): float { return $this->discountRate; }
    public function notes(): ?string { return $this->notes; }
    public function terms(): ?string { return $this->terms; }
    public function status(): string { return $this->status; }
    public function subtotal(): Money { return $this->subtotal; }
    public function taxAmount(): Money { return $this->taxAmount; }
    public function discountAmount(): Money { return $this->discountAmount; }
    public function total(): Money { return $this->total; }
    public function createdAt(): Carbon { return $this->createdAt; }
    public function updatedAt(): Carbon { return $this->updatedAt; }
    public function template(): TemplateStyle { return $this->template; }
    public function colors(): ThemeColors { return $this->colors; }
    public function signature(): ?string { return $this->signature; }
    public function preparedBy(): ?string { return $this->preparedBy; }
    
    public function setPreparedBy(?string $preparedBy): void { $this->preparedBy = $preparedBy; $this->touch(); }

    public function toArray(): array
    {
        return [
            'id' => $this->id->value(),
            'user_id' => $this->userId,
            'session_id' => $this->sessionId,
            'type' => $this->type->value,
            'type_label' => $this->type->label(),
            'document_number' => $this->documentNumber->value(),
            'business_info' => $this->businessInfo->toArray(),
            'client_info' => $this->clientInfo->toArray(),
            'issue_date' => $this->issueDate->format('Y-m-d'),
            'due_date' => $this->dueDate?->format('Y-m-d'),
            'currency' => $this->currency,
            'items' => array_map(fn(LineItem $item) => $item->toArray(), $this->items),
            'tax_rate' => $this->taxRate,
            'discount_rate' => $this->discountRate,
            'subtotal' => $this->subtotal->amount(),
            'tax_amount' => $this->taxAmount->amount(),
            'discount_amount' => $this->discountAmount->amount(),
            'total' => $this->total->amount(),
            'notes' => $this->notes,
            'terms' => $this->terms,
            'status' => $this->status,
            'template' => $this->template->value,
            'colors' => $this->colors->toArray(),
            'signature' => $this->signature,
            'prepared_by' => $this->preparedBy,
            'created_at' => $this->createdAt->toIso8601String(),
            'updated_at' => $this->updatedAt->toIso8601String(),
        ];
    }
}
