<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use App\Infrastructure\Persistence\Eloquent\GrowFinanceJournalEntryModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GrowFinanceSyncLogModel extends Model
{
    protected $table = 'cms_growfinance_sync_log';

    protected $fillable = [
        'company_id',
        'cms_entity_type',
        'cms_entity_id',
        'growfinance_journal_entry_id',
        'sync_status',
        'sync_attempt_count',
        'last_sync_at',
        'error_message',
    ];

    protected $casts = [
        'last_sync_at' => 'datetime',
        'sync_attempt_count' => 'integer',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'company_id');
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(GrowFinanceJournalEntryModel::class, 'growfinance_journal_entry_id');
    }

    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeForEntity($query, string $entityType, int $entityId)
    {
        return $query->where('cms_entity_type', $entityType)
                     ->where('cms_entity_id', $entityId);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('sync_status', $status);
    }

    public function scopePending($query)
    {
        return $query->where('sync_status', 'pending');
    }

    public function scopeSynced($query)
    {
        return $query->where('sync_status', 'synced');
    }

    public function scopeFailed($query)
    {
        return $query->where('sync_status', 'failed');
    }

    public function isSynced(): bool
    {
        return $this->sync_status === 'synced';
    }

    public function isFailed(): bool
    {
        return $this->sync_status === 'failed';
    }

    public function isPending(): bool
    {
        return $this->sync_status === 'pending';
    }

    public function markAsSynced(int $journalEntryId): void
    {
        $this->update([
            'sync_status' => 'synced',
            'growfinance_journal_entry_id' => $journalEntryId,
            'last_sync_at' => now(),
            'error_message' => null,
        ]);
    }

    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'sync_status' => 'failed',
            'sync_attempt_count' => $this->sync_attempt_count + 1,
            'last_sync_at' => now(),
            'error_message' => $errorMessage,
        ]);
    }

    public function incrementAttempt(): void
    {
        $this->increment('sync_attempt_count');
    }
}
