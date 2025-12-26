<?php

namespace App\Infrastructure\GrowBuilder\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GrowBuilderInvoice extends Model
{
    protected $table = 'growbuilder_invoices';

    protected $fillable = [
        'site_id',
        'order_id',
        'invoice_number',
        'customer_name',
        'customer_phone',
        'customer_email',
        'customer_address',
        'items',
        'subtotal',
        'tax_amount',
        'tax_rate',
        'discount_amount',
        'total',
        'status',
        'issue_date',
        'due_date',
        'notes',
        'terms',
        'pdf_path',
        'sent_at',
        'paid_at',
    ];

    protected $casts = [
        'items' => 'array',
        'subtotal' => 'integer',
        'tax_amount' => 'integer',
        'tax_rate' => 'decimal:2',
        'discount_amount' => 'integer',
        'total' => 'integer',
        'issue_date' => 'date',
        'due_date' => 'date',
        'sent_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    public function site(): BelongsTo
    {
        return $this->belongsTo(GrowBuilderSite::class, 'site_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(GrowBuilderOrder::class, 'order_id');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'sent')
            ->where('due_date', '<', now())
            ->whereNull('paid_at');
    }

    public function getTotalInKwachaAttribute(): float
    {
        return $this->total / 100;
    }

    public function getFormattedTotalAttribute(): string
    {
        return 'K' . number_format($this->total_in_kwacha, 2);
    }

    public function isOverdue(): bool
    {
        return $this->due_date && 
               $this->due_date->isPast() && 
               $this->status === 'sent' &&
               !$this->paid_at;
    }

    public function isPaid(): bool
    {
        return $this->paid_at !== null;
    }
}
