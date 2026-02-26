<?php

namespace App\Infrastructure\Storage\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class FileShare extends Model
{
    use HasUuids;

    protected $fillable = [
        'file_id',
        'user_id',
        'share_token',
        'password',
        'expires_at',
        'max_downloads',
        'download_count',
        'is_active',
        'allow_preview',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
        'allow_preview' => 'boolean',
    ];

    public function file(): BelongsTo
    {
        return $this->belongsTo(StorageFile::class, 'file_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function hasReachedDownloadLimit(): bool
    {
        return $this->max_downloads && $this->download_count >= $this->max_downloads;
    }

    public function canAccess(): bool
    {
        return $this->is_active && !$this->isExpired() && !$this->hasReachedDownloadLimit();
    }

    public function incrementDownloadCount(): void
    {
        $this->increment('download_count');
    }
}
