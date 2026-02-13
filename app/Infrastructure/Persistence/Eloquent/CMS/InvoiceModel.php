<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InvoiceModel extends Model
{
    protected $table = 'cms_invoices';

    protected $fillable = [
        'company_id',
        'customer_id',
        'job_id',
        'recurring_invoice_id',
        'invoice_number',
        'invoice_date',
        'due_date',
        'subtotal',
        'tax_amount',
        'total_amount',
        'amount_paid',
        'amount_due',
        'status',
        'notes',
        'terms',
        'created_by',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'amount_due' => 'decimal:2',
    ];

    // Relationships
    public function company(): BelongsTo
    {
        return $this->belongsTo(CompanyModel::class, 'company_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(CustomerModel::class, 'customer_id');
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(JobModel::class, 'job_id');
    }

    public function recurringInvoice(): BelongsTo
    {
        return $this->belongsTo(RecurringInvoiceModel::class, 'recurring_invoice_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(CmsUserModel::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItemModel::class, 'invoice_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(PaymentModel::class, 'invoice_id');
    }

    // Scopes
    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', '!=', 'paid')
            ->where('due_date', '<', now());
    }

    // Helper methods
    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isOverdue(): bool
    {
        return !$this->isPaid() && $this->due_date < now();
    }

    public function updateAmountPaid(float $amount): void
    {
        $this->amount_paid += $amount;
        $this->amount_due = $this->total_amount - $this->amount_paid;

        if ($this->amount_due <= 0) {
            $this->status = 'paid';
        } elseif ($this->amount_paid > 0) {
            $this->status = 'partial';
        }

        $this->save();
    }
}
