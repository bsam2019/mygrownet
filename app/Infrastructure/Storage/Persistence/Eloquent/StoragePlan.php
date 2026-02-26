<?php

namespace App\Infrastructure\Storage\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StoragePlan extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'quota_bytes',
        'max_file_size_bytes',
        'allowed_mime_types',
        'allow_sharing',
        'allow_public_profile_files',
        'is_active',
        'product_id',
    ];

    protected $casts = [
        'quota_bytes' => 'integer',
        'max_file_size_bytes' => 'integer',
        'allowed_mime_types' => 'array',
        'allow_sharing' => 'boolean',
        'allow_public_profile_files' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function subscriptions(): HasMany
    {
        return $this->hasMany(UserStorageSubscription::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Product::class);
    }

    public function getFormattedQuotaAttribute(): string
    {
        $gb = $this->quota_bytes / (1024 * 1024 * 1024);
        return $gb >= 1 ? "{$gb} GB" : ($this->quota_bytes / (1024 * 1024)) . " MB";
    }

    public function getFormattedMaxFileSizeAttribute(): string
    {
        $mb = $this->max_file_size_bytes / (1024 * 1024);
        return $mb >= 1 ? "{$mb} MB" : ($this->max_file_size_bytes / 1024) . " KB";
    }
}
