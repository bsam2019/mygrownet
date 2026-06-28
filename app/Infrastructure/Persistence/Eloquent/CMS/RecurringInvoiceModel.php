<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RecurringInvoiceModel extends Model
{
    protected $table = 'cms_recurring_invoices';

    protected $fillable = [
        'company_id',
        'customer_id',
        'job_id',
        'title',
        'description',
        'items',
        'subtotal',
        'tax_amount',
        'total',
        'frequency',
        'interval',
        'start_date',
        'end_date',
        'max_occurrences',
        'occurrences_count',
        'next_generation_date',
        'status',
        'auto_send_email',
        'email_to',
        'email_cc',
        'payment_terms_days',
        'notes',
        'last_generated_at',
    ];

    protected $casts = [
        'items' => 'array',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'interval' => 'integer',
        'max_occurrences' => 'integer',
        'occurrences_count' => 'integer',
        'payment_terms_days' => 'integer',
        'auto_send_email' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
        'next_generation_date' => 'date',
        'last_generated_at' => 'datetime',
    ];

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

    public function history(): HasMany
    {
        return $this->hasMany(RecurringInvoiceHistoryModel::class, 'recurring_invoice_id');
    }

    public function generatedInvoices(): HasMany
    {
        return $this->hasMany(InvoiceModel::class, 'recurring_invoice_id');
    }
}
