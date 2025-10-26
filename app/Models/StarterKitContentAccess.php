<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StarterKitContentAccess extends Model
{
    use HasFactory;

    protected $table = 'starter_kit_content_access';

    protected $fillable = [
        'user_id',
        'content_type',
        'content_id',
        'content_name',
        'first_accessed_at',
        'last_accessed_at',
        'completion_status',
        'completion_date',
        'progress_percentage',
    ];

    protected $casts = [
        'first_accessed_at' => 'datetime',
        'last_accessed_at' => 'datetime',
        'completion_date' => 'datetime',
        'progress_percentage' => 'integer',
    ];

    /**
     * Get the user who accessed the content.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Track content access.
     */
    public function trackAccess(): void
    {
        $now = now();
        
        if (!$this->first_accessed_at) {
            $this->first_accessed_at = $now;
        }
        
        $this->last_accessed_at = $now;
        
        if ($this->completion_status === 'not_started') {
            $this->completion_status = 'in_progress';
        }
        
        $this->save();
    }

    /**
     * Mark content as completed.
     */
    public function markAsCompleted(): void
    {
        $this->update([
            'completion_status' => 'completed',
            'completion_date' => now(),
            'progress_percentage' => 100,
        ]);
    }

    /**
     * Update progress percentage.
     */
    public function updateProgress(int $percentage): void
    {
        $this->update([
            'progress_percentage' => min(100, max(0, $percentage)),
            'completion_status' => $percentage >= 100 ? 'completed' : 'in_progress',
            'completion_date' => $percentage >= 100 ? now() : null,
        ]);
    }

    /**
     * Check if content is completed.
     */
    public function isCompleted(): bool
    {
        return $this->completion_status === 'completed';
    }

    /**
     * Check if content is in progress.
     */
    public function isInProgress(): bool
    {
        return $this->completion_status === 'in_progress';
    }

    /**
     * Scope for completed content.
     */
    public function scopeCompleted($query)
    {
        return $query->where('completion_status', 'completed');
    }

    /**
     * Scope for in-progress content.
     */
    public function scopeInProgress($query)
    {
        return $query->where('completion_status', 'in_progress');
    }

    /**
     * Scope by content type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('content_type', $type);
    }
}
