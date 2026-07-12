<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BizBoostClientWalletModel extends Model
{
    protected $table = 'bizboost_client_wallets';

    protected $fillable = [
        'user_id',
        'balance',
        'locked_balance',
        'currency',
    ];

    protected $casts = [
        'balance' => 'decimal:4',
        'locked_balance' => 'decimal:4',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(BizBoostWalletTransactionModel::class, 'wallet_id');
    }
}
