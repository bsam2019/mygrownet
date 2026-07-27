<?php

namespace App\Domain\PlatformPayments\Infrastructure;

use Illuminate\Database\Eloquent\Model;

class PaymentTransactionModel extends Model
{
    protected $table = 'payment_transactions';

    protected $fillable = [
        'organization_id',
        'amount',
        'currency',
        'payment_method',
        'status',
        'provider_transaction_id',
        'provider_reference',
        'provider',
        'fee',
        'settled_amount',
        'settled_at',
        'metadata',
        'failure_reason',
        'attempt_count',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'fee' => 'decimal:2',
            'settled_amount' => 'decimal:2',
            'metadata' => 'array',
            'settled_at' => 'datetime',
        ];
    }

    public function attempts()
    {
        return $this->hasMany(PaymentAttemptModel::class, 'transaction_id');
    }
}
