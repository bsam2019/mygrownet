<?php

namespace App\Infrastructure\Persistence\Eloquent\Promotion;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class PromotionalCardShareModel extends Model
{
    use HasFactory;

    protected $table = 'promotional_card_shares';

    protected $fillable = [
        'promotional_card_id',
        'user_id',
        'platform',
        'ip_address',
        'shared_at',
    ];

    protected $casts = [
        'shared_at' => 'datetime',
    ];

    /**
     * Get the promotional card
     */
    public function card(): BelongsTo
    {
        return $this->belongsTo(PromotionalCardModel::class, 'promotional_card_id');
    }

    /**
     * Get the user who shared
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope: Get shares for today
     */
    public function scopeToday($query)
    {
        return $query->whereDate('shared_at', today());
    }

    /**
     * Scope: Get shares for a specific user
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }
}
