<?php

namespace App\Infrastructure\Persistence\Eloquent\Notification;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class NotificationModel extends Model
{
    protected $table = 'notifications';

    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false; // No updated_at column

    protected $fillable = [
        'id',
        'notifiable_type',
        'notifiable_id',
        'type',
        'module',
        'category',
        'title',
        'message',
        'action_url',
        'action_text',
        'data',
        'priority',
        'read_at',
        'archived_at',
        'expires_at',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
        'archived_at' => 'datetime',
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('notifiable_type', User::class)
            ->where('notifiable_id', $userId);
    }

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }

    public function scopeNotArchived($query)
    {
        return $query->whereNull('archived_at');
    }

    public function scopeByModule($query, string $module)
    {
        return $query->where('module', $module);
    }

    public function markAsRead(): void
    {
        $this->update(['read_at' => now()]);
    }

    public function markAllAsRead(): void
    {
        $this->update(['read_at' => now()]);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }
}
