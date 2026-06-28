<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CmsSyncLog extends Model
{
    protected $table = 'cms_sync_log';

    protected $fillable = [
        'cms_entity_type',
        'cms_entity_id',
        'transaction_id',
        'sync_status',
        'sync_error',
        'synced_at',
    ];

    protected $casts = [
        'synced_at' => 'datetime',
    ];

    /**
     * Get the transaction
     */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    /**
     * Scope for synced records
     */
    public function scopeSynced($query)
    {
        return $query->where('sync_status', 'synced');
    }

    /**
     * Scope for failed records
     */
    public function scopeFailed($query)
    {
        return $query->where('sync_status', 'failed');
    }

    /**
     * Scope for pending records
     */
    public function scopePending($query)
    {
        return $query->where('sync_status', 'pending');
    }

    /**
     * Scope for expense syncs
     */
    public function scopeExpenses($query)
    {
        return $query->where('cms_entity_type', 'expense');
    }
}
