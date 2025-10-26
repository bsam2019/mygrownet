<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class StarterKitUnlock extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'content_item',
        'content_category',
        'unlock_date',
        'unlocked_at',
        'viewed_at',
        'is_unlocked',
    ];

    protected $casts = [
        'unlock_date' => 'date',
        'unlocked_at' => 'datetime',
        'viewed_at' => 'datetime',
        'is_unlocked' => 'boolean',
    ];

    /**
     * Get the user.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if content should be unlocked.
     */
    public function shouldUnlock(): bool
    {
        return !$this->is_unlocked && Carbon::today()->greaterThanOrEqualTo($this->unlock_date);
    }

    /**
     * Unlock the content.
     */
    public function unlock(): void
    {
        if (!$this->is_unlocked) {
            $this->update([
                'is_unlocked' => true,
                'unlocked_at' => now(),
            ]);
        }
    }

    /**
     * Mark as viewed.
     */
    public function markAsViewed(): void
    {
        if (!$this->viewed_at) {
            $this->update([
                'viewed_at' => now(),
            ]);
        }
    }

    /**
     * Get days until unlock.
     */
    public function daysUntilUnlock(): int
    {
        if ($this->is_unlocked) {
            return 0;
        }
        
        return max(0, Carbon::today()->diffInDays($this->unlock_date, false));
    }

    /**
     * Scope for unlocked content.
     */
    public function scopeUnlocked($query)
    {
        return $query->where('is_unlocked', true);
    }

    /**
     * Scope for locked content.
     */
    public function scopeLocked($query)
    {
        return $query->where('is_unlocked', false);
    }

    /**
     * Scope for content ready to unlock.
     */
    public function scopeReadyToUnlock($query)
    {
        return $query->where('is_unlocked', false)
                    ->whereDate('unlock_date', '<=', Carbon::today());
    }

    /**
     * Scope by category.
     */
    public function scopeOfCategory($query, string $category)
    {
        return $query->where('content_category', $category);
    }
}
