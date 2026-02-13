<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentAllocationModel extends Model
{
    protected $table = 'cms_payment_allocations';

    protected $fillable = [
        'payment_id',
        'invoice_id',
        'amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function payment(): BelongsTo
    {
        return $this->belongsTo(PaymentModel::class, 'payment_id');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(InvoiceModel::class, 'invoice_id');
    }
}
