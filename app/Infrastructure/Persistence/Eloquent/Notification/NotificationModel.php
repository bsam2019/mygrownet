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
        'user_id',
        'type',
        'category',
        'module',
        'title',
        'message',
        'action_url',
        'action_text',
        'data',
        'priority',
        'read_at',
        'archived_at',
        'expires_at',
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

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByModule($query, string $module)
    {
        return $query->where('module', $module);
    }

    public function scopeForGrowFinance($query)
    {
        return $query->where('module', 'growfinance');
    }

    public function scopeForGrowBiz($query)
    {
        return $query->where('module', 'growbiz');
    }

    public function scopeForMyGrowNet($query)
    {
        return $query->where('module', 'mygrownet');
    }

    public function scopeForCore($query)
    {
        return $query->where('module', 'core');
    }
}
