<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecurringInvoiceHistoryModel extends Model
{
    protected $table = 'cms_recurring_invoice_history';

    protected $fillable = [
        'recurring_invoice_id',
        'invoice_id',
        'generated_date',
        'email_sent',
        'email_sent_at',
    ];

    protected $casts = [
        'generated_date' => 'date',
        'email_sent' => 'boolean',
        'email_sent_at' => 'datetime',
    ];

    public function recurringInvoice(): BelongsTo
    {
        return $this->belongsTo(RecurringInvoiceModel::class, 'recurring_invoice_id');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(InvoiceModel::class, 'invoice_id');
    }
}
