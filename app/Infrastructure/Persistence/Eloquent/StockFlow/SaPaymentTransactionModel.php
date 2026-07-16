<?php

namespace App\Infrastructure\Persistence\Eloquent\StockFlow;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SaPaymentTransactionModel extends Model
{
    protected $table = 'sa_payment_transactions';
    protected $fillable = ['sa_company_id', 'payable_type', 'payable_id', 'gateway', 'transaction_id', 'amount', 'currency', 'status', 'gateway_response'];
    protected $casts = [
        'amount' => 'decimal:2',
        'gateway_response' => 'array',
    ];

    public function company(): BelongsTo { return $this->belongsTo(SaCompanyModel::class, 'sa_company_id'); }
    public function payable(): MorphTo { return $this->morphTo(); }
}
