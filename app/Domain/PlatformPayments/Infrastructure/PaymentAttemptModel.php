<?php

namespace App\Domain\PlatformPayments\Infrastructure;

use Illuminate\Database\Eloquent\Model;

class PaymentAttemptModel extends Model
{
    protected $table = 'platform_payment_attempts';

    protected $fillable = [
        'transaction_id',
        'attempt_number',
        'status',
        'provider_response',
        'error_message',
        'scheduled_at',
        'attempted_at',
    ];

    protected function casts(): array
    {
        return [
            'provider_response' => 'array',
            'scheduled_at' => 'datetime',
            'attempted_at' => 'datetime',
        ];
    }

    public function transaction()
    {
        return $this->belongsTo(PaymentTransactionModel::class, 'transaction_id');
    }
}
