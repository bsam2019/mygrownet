<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class BizBoostWalletTransactionModel extends Model
{
    protected $table = 'bizboost_wallet_transactions';

    protected $fillable = [
        'wallet_id',
        'type',
        'amount',
        'balance_before',
        'balance_after',
        'currency',
        'reference',
        'description',
        'payable_type',
        'payable_id',
        'status',
        'meta',
    ];

    protected $casts = [
        'amount' => 'decimal:4',
        'balance_before' => 'decimal:4',
        'balance_after' => 'decimal:4',
        'meta' => 'json',
    ];

    public function payable(): MorphTo
    {
        return $this->morphTo();
    }
}
