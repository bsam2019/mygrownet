<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuickInvoiceDocument extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'session_id',
        'document_type',
        'document_number',
        'business_name',
        'business_address',
        'business_phone',
        'business_email',
        'business_logo',
        'business_tax_number',
        'business_website',
        'client_name',
        'client_address',
        'client_phone',
        'client_email',
        'issue_date',
        'due_date',
        'currency',
        'subtotal',
        'tax_rate',
        'tax_amount',
        'discount_rate',
        'discount_amount',
        'total',
        'notes',
        'terms',
        'status',
        'template',
        'colors',
        'signature',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_rate' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'colors' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuickInvoiceItem::class, 'document_id')->orderBy('sort_order');
    }

    public function getTypeLabel(): string
    {
        return match ($this->document_type) {
            'invoice' => 'Invoice',
            'delivery_note' => 'Delivery Note',
            'quotation' => 'Quotation',
            'receipt' => 'Receipt',
            default => ucfirst($this->document_type),
        };
    }

    public function getCurrencySymbol(): string
    {
        return match ($this->currency) {
            'ZMW' => 'K',
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'ZAR' => 'R',
            default => $this->currency,
        };
    }

    public function getFormattedTotal(): string
    {
        return $this->getCurrencySymbol() . ' ' . number_format($this->total, 2);
    }
}
