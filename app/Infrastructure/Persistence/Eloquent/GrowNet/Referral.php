<?php

namespace App\Infrastructure\Persistence\Eloquent\GrowNet;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class Referral extends Model
{
    protected $fillable = [
        'referrer_id',
        'referred_id',
    ];

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    public function referred(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_id');
    }
}
