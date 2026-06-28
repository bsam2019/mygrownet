<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LgrManualAward extends Model
{
    protected $fillable = [
        'user_id',
        'awarded_by',
        'amount',
        'award_type',
        'reason',
        'credited',
        'credited_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'credited' => 'boolean',
        'credited_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function awardedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'awarded_by');
    }
}
