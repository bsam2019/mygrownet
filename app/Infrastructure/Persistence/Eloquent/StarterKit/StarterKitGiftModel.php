<?php

namespace App\Infrastructure\Persistence\Eloquent\StarterKit;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StarterKitGiftModel extends Model
{
    protected $table = 'starter_kit_gifts';

    protected $fillable = [
        'gifter_id',
        'recipient_id',
        'tier',
        'amount',
        'purchase_id',
        'status',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function gifter(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'gifter_id');
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'recipient_id');
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(StarterKitPurchaseModel::class, 'purchase_id');
    }
}
