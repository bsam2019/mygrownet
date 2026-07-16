<?php

namespace App\Infrastructure\Persistence\Eloquent\StockFlow;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaCustomerModel extends Model
{
    protected $table = 'sa_customers';
    protected $fillable = [
        'sa_company_id', 'name', 'phone', 'email', 'address', 'city', 'country',
        'credit_limit', 'payment_terms', 'notes',
    ];
    protected $casts = [
        'credit_limit' => 'decimal:2',
    ];

    public function company(): BelongsTo { return $this->belongsTo(SaCompanyModel::class, 'sa_company_id'); }
}
