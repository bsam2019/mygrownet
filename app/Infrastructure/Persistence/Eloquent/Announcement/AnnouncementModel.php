<?php

namespace App\Infrastructure\Persistence\Eloquent\Announcement;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Announcement Eloquent Model
 * 
 * Data layer representation
 */
class AnnouncementModel extends Model
{
    protected $table = 'announcements';

    protected $fillable = [
        'title',
        'message',
        'type',
        'target_audience',
        'user_id',
        'is_urgent',
        'is_active',
        'starts_at',
        'expires_at',
        'created_by',
    ];

    protected $casts = [
        'is_urgent' => 'boolean',
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function readers(): BelongsToMany
    {
        return $this->belongsToMany(
            \App\Models\User::class,
            'announcement_reads',
            'announcement_id',
            'user_id'
        )->withTimestamps();
    }

    public function reads()
    {
        return $this->hasMany(AnnouncementReadModel::class, 'announcement_id');
    }

    /**
     * Scope for active announcements
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('starts_at')
                  ->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>=', now());
            });
    }
}
